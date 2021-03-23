<?php

declare(strict_types=1);

namespace Olcs\Mvc\Strategy\Validation;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\Http\Response;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\MvcEvent;

/**
 * @see ValidationStrategyFactory
 */
class ValidationStrategy extends AbstractListenerAggregate
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var FlashMessenger
     */
    protected $flashMessenger;

    /**
     * @param TranslatorInterface $translator
     * @param FlashMessenger $flashMessenger
     */
    public function __construct(TranslatorInterface $translator, FlashMessenger $flashMessenger)
    {
        $this->translator = $translator;
        $this->flashMessenger = $flashMessenger;
    }

    public function attach(\Laminas\EventManager\EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'handleError']);
    }

    /**
     * @param MvcEvent $event
     * @return mixed
     */
    public function handleError(MvcEvent $event)
    {
        $exception = $event->getParam('exception');

        if (! ($exception instanceof ValidationExceptionInterface)) {
            return;
        }

        foreach ($exception->getValidationMessages() as $inputFieldName => $messages) {
            foreach ($messages as $message) {
                $this->flashMessenger->addErrorMessage($this->translator->translate($message));
            }
        }

        // @todo flash input `$this->flashMessenger->flashInput($input)` - this needs to be associated with the controller
//        $this->flashMessenger->addMessage(json_encode($exception->getInput()), 'HTTP_VALIDATION');

        $routeMatch = $event->getRouteMatch();
        $route = $routeMatch->getMatchedRouteName();
        $router = $event->getRouter();
        $url = $router->assemble($routeMatch->getParams(), ['name' => $route]);
        $response = new Response();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $event->setResult($response);
        return $response;
    }
}
