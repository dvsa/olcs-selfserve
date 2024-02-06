<?php

declare(strict_types=1);

namespace Olcs\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\Lva\AbstractController;
use Common\FeatureToggle;
use Common\Form\Form;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Create;
use Dvsa\Olcs\Transfer\Command\Messaging\Message\Create as CreateMessageCommand;
use Dvsa\Olcs\Transfer\Query\Messaging\Messages\ByConversation as ByConversationQuery;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByOrganisation as ByOrganisationQuery;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Form\Model\Form\Message\Reply as ReplyForm;
use Olcs\Form\Model\Form\Message\Create as CreateForm;

class ConversationsController extends AbstractController implements ToggleAwareInterface
{
    use Lva\Traits\ExternalControllerTrait;

    protected $toggleConfig = [
        'default' => [FeatureToggle::MESSAGING],
    ];

    protected FlashMessengerHelperService $flashMessengerHelper;
    protected TableFactory $tableFactory;
    protected FormHelperService $formHelperService;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        TableFactory $tableFactory,
        FormHelperService $formHelperService
    ) {
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->tableFactory = $tableFactory;
        $this->formHelperService = $formHelperService;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    public function indexAction(): ViewModel
    {
        $params = [
            'page'         => $this->params()->fromQuery('page', 1),
            'limit'        => $this->params()->fromQuery('limit', 10),
            'sort'         => $this->params()->fromQuery('sort', 'd.issuedDate'),
            'order'        => $this->params()->fromQuery('order', 'DESC'),
            'organisation' => $this->getCurrentOrganisationId(),
            'query'        => $this->params()->fromQuery(),
        ];

        $response = $this->handleQuery(ByOrganisationQuery::create($params));

        if ($response->isOk()) {
            $messages = $response->getResult();
        } else {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
            $messages = [];
        }

        $table = $this->tableFactory
            ->buildTable('messages', $messages, $params);

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('messages');

        return $view;
    }

    public function addAction(): ViewModel
    {
        $form = $this->formHelperService->createForm(CreateForm::class, true, false);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                return $this->submitConversation($form);
            }
        }

        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setTemplate('messages-new');

        return $view;
    }

    private function submitConversation(\Laminas\Form\Form $form)
    {
        $response = $this->handleCommand($this->mapFormDataToCommand($form));
        if (!$response->isOk()) {
            $this->flashMessengerHelper->addErrorMessage('There was an server error when submitting your conversation; please try later');
            return $this->addAction();
        }

        $conversationId = $response->getResult()['id']['conversation'] ?? null;
        if (empty($conversationId)) {
            $this->flashMessengerHelper->addErrorMessage('There was an server error when submitting your conversation; please try later');
            return $this->addAction();
        }

        $this->flashMessengerHelper->addSuccessMessage('Conversation was created successfully');
        return $this->redirect()->toRoute('conversations/view', ['conversationId' => $conversationId]);
    }

    private function mapFormDataToCommand(\Laminas\Form\Form $form): Create
    {
        $data = $form->getData();
        $processedData = [
            'messageSubject' => $data['form-actions']['messageSubject'],
            'messageContent' => $data['form-actions']['messageContent'],
        ];

        $appOrLicNoPrefix = substr($data['form-actions']['appOrLicNo'], 0, 1);
        $appOrLicNoSuffix = substr($data['form-actions']['appOrLicNo'], 1);
        switch($appOrLicNoPrefix) {
            case 'L':
                $processedData['licence'] = $appOrLicNoSuffix;
                break;
            case 'A':
                $processedData['application'] = $appOrLicNoSuffix;
                break;
            default:
                throw new \Exception('Invalid prefix on appOrLicNo');
        }

        return Create::create($processedData);
    }

    /** @return ViewModel|Response */
    public function viewAction()
    {
        $params = [
            'page'         => $this->params()->fromQuery('page', 1),
            'limit'        => $this->params()->fromQuery('limit', 10),
            'conversation' => $this->params()->fromRoute('conversationId'),
            'query'        => $this->params()->fromQuery(),
        ];

        $response = $this->handleQuery(ByConversationQuery::create($params));

        if ($response->isOk()) {
            $messages = $response->getResult();
        } else {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
            $messages = [];
        }

        $form = $this->formHelperService->createForm(ReplyForm::class, true, false);
        $this->formHelperService->setFormActionFromRequest($form, $this->getRequest());

        $table = $this->tableFactory
            ->buildTable('messages-view', $messages, $params);

        $view = new ViewModel(
            [
                'table' => $table,
                'form'  => $form,
            ],
        );
        $view->setTemplate('messages-view');

        if ($this->getRequest()->isPost() && $this->params()->fromPost('action') === 'reply') {
            return $this->parseReply($view, $form);
        }

        return $view;
    }

    /** @return Response|ViewModel */
    protected function parseReply(ViewModel $view, Form $form)
    {
        $form->setData((array)$this->params()->fromPost());
        $form->get('id')->setValue($this->params()->fromRoute('conversation'));

        if (!$form->isValid()) {
            return $view;
        }

        $response = $this->handleCommand(
            CreateMessageCommand::create(
                [
                    'conversation'   => $this->params()->fromRoute('conversationId'),
                    'messageContent' => $form->get('form-actions')->get('reply')->getValue(),
                ],
            ),
        );

        if ($response->isOk()) {
            $this->flashMessengerHelper->addSuccessMessage('Reply submitted successfully');
            return $this->redirect()->toRoute('conversations/view', $this->params()->fromRoute());
        }

        $this->handleErrors($response->getResult());

        return parent::indexAction();
    }
}