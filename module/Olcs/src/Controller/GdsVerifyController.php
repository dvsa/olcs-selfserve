<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Exception\BadRequestException;
use Dvsa\Olcs\Transfer\Command\GdsVerify\ProcessSignatureResponse;
use Dvsa\Olcs\Transfer\Query\GdsVerify\GetAuthRequest;
use Exception;
use Laminas\Cache\Storage\Adapter\Redis;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use Olcs\DTO\Verify\DigitalSignature;
use Olcs\Logging\Log\Logger;
use RuntimeException;
use ZfcRbac\Exception\UnauthorizedException;

/**
 * GdsVerifyController Controller
 */
class GdsVerifyController extends AbstractController
{
    const CACHE_PREFIX = "verify:";

    /**
     * @var StorageInterface
     */
    private $cache;

    public function onDispatch(MvcEvent $e)
    {
        $this->cache = $this->getServiceLocator()->get(Redis::class);
        return parent::onDispatch($e);
    }

    /**
     * Display Form to initiate the GDS Verify identification process
     *
     * @return ViewModel
     * @throws Exception
     */
    public function initiateRequestAction()
    {
        $response = $this->handleQuery(GetAuthRequest::create([]));

        if (!$response->isOk()) {
            throw new Exception("");
        }
        $result = $response->getResult();
        if ($result['enabled'] !== true) {
            throw new RuntimeException('Verify is currently disabled');
        }

        $verifyRequestId = $this->getRootAttributeFromSaml($result['samlRequest'], 'ID');
        $this->createAndStoreDigitalSignature(
            $this->getTypeOfRequest($this->params()->fromRoute()),
            $verifyRequestId
        );
        $this->whitelistUserVerifyRequest($verifyRequestId);

        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('VerifyRequest');
        $form->setAttribute('action', $result['url']);
        $form->get('SAMLRequest')->setValue($result['samlRequest']);

        $this->getServiceLocator()->get('Script')->loadFile('verify-request');

        return new ViewModel(array('form' => $form));
    }

    /**
     * Process the request from GDS Verify and forwards to Process Signature Action.
     *
     * This is required due to SameSite Cookies and not compromising by converting our cookies to third-party.
     *
     * @return HttpResponse
     * @throws UnauthorizedException|Exception
     */
    public function processResponseAction(): HttpResponse
    {
        $samlResponse = $this->getRequest()->getPost('SAMLResponse', null);
        if (is_null($samlResponse)) {
            throw new UnauthorizedException('Missing samlResponse');
        }

        $id = $this->getRootAttributeFromSaml($samlResponse, 'InResponseTo');
        $verifyJourneyKey = $this->generateVerifyJourneyKey($id);
        if (!empty($this->cache->removeItems([$verifyJourneyKey]))) {
            throw new UnauthorizedException('Invalid verify journey id');
        }

        $key = $this->generateSamlKey($samlResponse);
        $this->cache->setItem($key, $samlResponse);

        return $this->redirect()->toRoute(
            'verify/process-signature',
            [],
            [
                'query' => [
                    'ref' => explode(':', $key)[1]
                ]
            ]
        );
    }

    /**
     * Process the GDS Verify SAML response
     *
     * @return HttpResponse
     * @throws BadRequestException
     * @throws Exception
     */
    public function processSignatureAction(): HttpResponse
    {
        $key = $this->getRequest()->getQuery('ref');
        if (!$this->validateRedisSamlResponseReferenceKey($key)) {
            throw new BadRequestException("Query parameter 'ref' ({$key}) is not a valid SHA1.");
        }

        $samlResponse = $this->cache->getItem(static::CACHE_PREFIX . $key);
        $inResponseTo = $this->getRootAttributeFromSaml($samlResponse, 'InResponseTo');

        $signature = $this->retrieveDigitalSignature($inResponseTo);

        /// GOT TO HERE
        ///
        ///

        Logger::debug("DigitalSignature retrieved:", $signature->toArray());

        $applicationId = $signature->getApplicationId() ?? false;
        $continuationDetailId = $signature->getContinuationDetailId() ?? false;
        $transportManagerApplicationId = $signature->getTransportManagerApplicationId() ?? false;
        $licenceId = $signature->getLicenceId() ?? false;
        $lva = $signature->getLva() ?? 'application';
        $role = $signature->getRole() ?? null;
        $verifyRequestId = $signature->getVerifyId() ?? null;

        if (empty($verifyRequestId)) {
            throw new BadRequestException("There is no `verifyId` on DigitalSignature.");
        }

        if (empty($inResponseTo)) {
            throw new BadRequestException("There is no `inResponseTo` in the samlResponse.");
        }

        if ($verifyRequestId !== $inResponseTo) {
            throw new UnauthorizedException("SamlResponse({$inResponseTo}) does not match SamlRequest({$verifyRequestId})");
        }

        $this->cache->removeItems([
            $this->generateActiveUserKey($this->currentUser()->getIdentity()->getUsername()),
            $key
        ]);

        $dto = ProcessSignatureResponse::create(['samlResponse' => $samlResponse]);

        if ($applicationId) {
            $dto->setApplication($applicationId);
        }
        if ($continuationDetailId) {
            $dto->setContinuationDetail($continuationDetailId);
        }

        if ($transportManagerApplicationId) {
            $dto->setTransportManagerApplication($transportManagerApplicationId);
            $dto->setRole($role);
        }

        if ($licenceId) {
            $dto->setLicence($licenceId);
        }

        $response = $this->handleCommand($dto);
        if (!$response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('undertakings_not_signed');
        }

        if ($applicationId && !$transportManagerApplicationId) {
            return $this->redirect()->toRoute(
                'lva-application/undertakings',
                ['application' => $applicationId]
            );
        }

        if ($continuationDetailId) {
            return $this->redirect()->toRoute(
                'continuation/declaration',
                ['continuationDetailId' => $continuationDetailId]
            );
        }

        /** @var  $transportManagerApplicationId */
        if ($transportManagerApplicationId) {
            return $this->redirect()->toRoute(
                'lva-' . $lva . '/transport_manager_confirmation',
                [
                    'child_id' => $transportManagerApplicationId,
                    'application' => $applicationId,
                    'action' => 'index'
                ]
            );
        }

        if ($licenceId) {
            return $this->redirect()->toRoute(
                'licence/surrender/confirmation',
                [
                    'licence' => $licenceId,
                    'action' => 'index'
                ]
            );
        }

        throw new RuntimeException('There was an error processing the signature response');
    }

    /**
     * Create a DigitalSignature and store in redis
     *
     * @param array $types
     * @param string $verifyId
     * @throw \RuntimeException
     */
    private function createAndStoreDigitalSignature(array $types, string $verifyId)
    {
        if (empty($types)) {
            throw new RuntimeException(
                'An entity identifier needs to be present, this is used to to calculate where'
                . ' to return to after completing Verify'
            );
        }

        $types[DigitalSignature::KEY_VERIFY_ID] = $verifyId;
        $digitalSignature = new DigitalSignature($types);
        $this->cache->setItem(static::CACHE_PREFIX . $verifyId, $digitalSignature->toArray());
        Logger::debug("DigitalSignature created:", $digitalSignature->toArray());
    }

    /**
     * @param $params
     * @return array
     */
    private function getTypeOfRequest($params): array
    {
        // remove controller and action keys from params
        return array_diff_assoc($params, ['controller' => self::class, 'action' => 'initiate-request']);
    }

    /**
     * Generate cache key for the samlResponse to be stored under
     *
     * @param string $samlResponse
     * @return string
     */
    private function generateSamlKey(string $samlResponse): string
    {
        $key = sha1($samlResponse);
        return static::CACHE_PREFIX . $key;
    }

    /**
     * Generate cache key to whitelist this verify journey
     *
     * @param string $id
     * @return string
     */
    private function generateVerifyJourneyKey(string $id): string
    {
        return static::CACHE_PREFIX . "activeJourneys:" . $id;
    }

    /**
     * Generate cache key to whitelist user for verify
     *
     * @param string $username
     * @return string
     */
    private function generateActiveUserKey(string $username): string
    {
        return static::CACHE_PREFIX . "activeUsers:" . $username;
    }

    /**
     * Extract an attribute from a SAML XML String Document
     *
     * @param string $samlString
     * @param string $attributeName
     * @return string
     * @throws Exception
     */
    protected function getRootAttributeFromSaml(string $samlString, string $attributeName): string
    {
        $samlString = base64_decode($samlString);
        $samlString = simplexml_load_string($samlString);

        if ($samlString === false) {
            throw new Exception("Unable to parse SAML XML String");
        }

        $attributes = (array)$samlString->attributes();

        if (!array_key_exists($attributeName, $attributes['@attributes'])) {
            throw new Exception("SAML XML String Document does not contain attribute '{$attributeName}' in the root.");
        }

        return (string)$attributes['@attributes'][$attributeName];
    }

    /**
     * Whitelist this users journey for verify
     *
     * @param string $verifyId
     */
    protected function whitelistUserVerifyRequest(string $verifyId): void
    {
        $activeUserKey = $this->generateActiveUserKey($this->currentUser()->getIdentity()->getUsername());
        $previousVerifyId = $this->cache->getItem($activeUserKey);
        if (!is_null($previousVerifyId)) {
            $this->cache->removeItems([
                    $this->generateVerifyJourneyKey($previousVerifyId),
                    $activeUserKey
                ]
            );
        }

        $this->cache->addItems([
            $activeUserKey => $verifyId,
            $this->generateVerifyJourneyKey($verifyId) => true
        ]);
    }

    private function validateRedisSamlResponseReferenceKey($key): bool
    {
        // Essentially, we verify the reference key is a SHA1.
        return (bool)preg_match('/^[0-9a-f]{40}$/i', $key);
    }

    /**
     * Retrieve a digital signature from redis based on a verify request id
     *
     * @param string $inResponseTo
     * @return DigitalSignature
     * @throws Exception
     */
    protected function retrieveDigitalSignature(string $inResponseTo): DigitalSignature
    {
        $signatureRedisKey = static::CACHE_PREFIX . $inResponseTo;
        $signature = $this->cache->getItem($signatureRedisKey);
        if (is_null($signature)) {
            throw new Exception("DigitalSignatureRedisKey '{$signatureRedisKey}' not found in redis.");
        }

        $this->cache->removeItem($signatureRedisKey);
        return new DigitalSignature($signature);
    }
}
