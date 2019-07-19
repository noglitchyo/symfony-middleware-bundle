<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\EventListener;

use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStackInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MiddlewareStackInjectorListener implements EventSubscriberInterface
{
    /**
     * @var MiddlewareStackInterface
     */
    private $middlewareStackHandler;

    public function __construct(MiddlewareStackInterface $middlewareStackHandler)
    {
        $this->middlewareStackHandler = $middlewareStackHandler;
    }

    /**
     * Compile the middleware stack with the initial controller as the default handler
     * and replace the controller with the compiled middleware stack.
     *
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $this->middlewareStackHandler
            ->withDefaultHandler($event->getController())
            ->compile($event->getRequest());

        $event->setController($controller);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -100],
        ];
    }
}
