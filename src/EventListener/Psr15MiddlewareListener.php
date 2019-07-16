<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\EventListener;

use NoGlitchYo\MiddlewareBundle\MiddlewareDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class Psr15MiddlewareListener implements EventSubscriberInterface
{
    /**
     * @var MiddlewareDispatcher
     */
    private $middlewareDispatcher;

    public function __construct(MiddlewareDispatcher $middlewareDispatcher)
    {
        $this->middlewareDispatcher = $middlewareDispatcher;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        $event->setController($this->middlewareDispatcher->dispatch($controller, $request));
    }

    //    public function onKernelRequest(RequestEvent $event)
    //    {
    //        $request = $event->getRequest();
    //        $httpRequest = $this->httpMessageFactory->createRequest($request);
    //
    //        // TODO: make the diff between httpRequest and processed request
    //
    //        $controller = new class($request) implements RequestHandlerInterface
    //        {
    //            private $originalRequest;
    //
    //            public function __construct(Request $originalRequest)
    //            {
    //                $this->originalRequest = $originalRequest;
    //            }
    //
    //            public function handle(ServerRequestInterface $request): ResponseInterface
    //            {
    //                // this is the request modified
    //                $this->originalRequest->attributes->set('_middleware_request', $request);
    //                throw new Exception('nope');
    //            }
    //        };
    //
    //        $callable = new RequestHandler($controller, $this->middlewareCollection);
    //
    //        try {
    //            $response = $callable($httpRequest);
    //            $event->setResponse($this->httpFoundationFactory->createResponse($response));
    //        } catch (Exception $e) {
    //
    //            //   var_dump($event->getRequest()->attributes->get('_psr7_server_request'));
    //            // no middleware with response
    //        }
    //    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -100],
            //            KernelEvents::REQUEST    => ['onKernelRequest', -100],
        ];
    }
}
