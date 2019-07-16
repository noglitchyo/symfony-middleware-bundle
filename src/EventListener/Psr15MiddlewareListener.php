<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\EventListener;

use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStackInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class Psr15MiddlewareListener implements EventSubscriberInterface
{
    /**
     * @var MiddlewareStackInterface
     */
    private $middlewareStackHandler;

    public function __construct(MiddlewareStackInterface $middlewareStackHandler)
    {
        $this->middlewareStackHandler = $middlewareStackHandler;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $request    = $event->getRequest();

        $event->setController($this->middlewareStackHandler->getRequestHandler($request, $controller));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -100],
        ];
    }
}
