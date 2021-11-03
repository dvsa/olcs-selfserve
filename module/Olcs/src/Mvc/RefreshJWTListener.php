<?php
declare(strict_types=1);

namespace Olcs\Mvc;

use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Transfer\Command\Auth\RefreshToken;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\MvcEvent;
use Laminas\Session\Container;

class RefreshJWTListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    protected CommandSender $commandSender;

    protected Container $session;

    /**
     * RefreshJWTListener constructor.
     * @param CommandSender $commandSender
     * @param Container $session
     */
    public function __construct(CommandSender $commandSender, Container $session)
    {
        $this->commandSender = $commandSender;
        $this->session = $session;
    }


    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], $priority);
    }

    public function onRoute(MvcEvent $e)
    {
        $request = $e->getRequest();

        if (!($request instanceof HttpRequest)) {
            return;
        }

        $token = $this->session->offsetGet('storage')['Token'] ?? null;
        if (empty($token)) {
            return;
        }

        // Only refresh the token if it expires within the next minute
        if ($token['expires'] - time() > 60) {
            return;
        }

        $refreshCommand = RefreshToken::create(['refreshToken' => $token['refresh_token']]);
        $response = $this->commandSender->send($refreshCommand);

        var_dump($response);exit;
    }
}
