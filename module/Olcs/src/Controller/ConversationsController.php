<?php

declare(strict_types=1);

namespace Olcs\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\Lva\AbstractController;
use Common\FeatureToggle;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Query\Messaging\Messages\ByConversation as ByConversationQuery;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByOrganisation as ByOrganisationQuery;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

class ConversationsController extends AbstractController implements ToggleAwareInterface
{
    use Lva\Traits\ExternalControllerTrait;

    protected $toggleConfig = [
        'default' => [FeatureToggle::MESSAGING],
    ];

    protected FlashMessengerHelperService $flashMessengerHelper;
    protected TableFactory                $tableFactory;

    public function __construct(
        NiTextTranslation           $niTextTranslationUtil,
        AuthorizationService        $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        TableFactory                $tableFactory
    )
    {
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->tableFactory = $tableFactory;

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
        if ($response === null) {
            return $this->notFoundAction();
        }

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

    public function viewAction(): ViewModel
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

        $table = $this->tableFactory
            ->buildTable('messages-view', $messages, $params);

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('messages-view');

        return $view;
    }
}
