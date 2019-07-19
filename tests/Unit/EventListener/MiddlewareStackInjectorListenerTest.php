<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Tests\Unit\EventListener;

use NoGlitchYo\MiddlewareBundle\EventListener\MiddlewareStackInjectorListener;
use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStack;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class MiddlewareStackInjectorListenerTest extends TestCase
{
    public function testOnKernelRequestCompileAndReplaceControllerWithMiddlewareStack(): void
    {
        $middlewareStackMock = $this->createMock(MiddlewareStack::class);

        $sut = new MiddlewareStackInjectorListener($middlewareStackMock);

        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $controller = function () {
        };
        $request    = new Request();
        $event      = new ControllerEvent($httpKernel, $controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $compiledRequestHandler = new class($this) implements RequestHandlerInterface
        {
            private $test;

            public function __construct(TestCase $test)
            {
                $this->test = $test;
            }

            public function __invoke(ServerRequestInterface $request): ResponseInterface
            {
                return $this->handle($request);
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $middlewareStackMock->expects($this->once())
            ->method('withDefaultHandler')
            ->with($controller)
            ->willReturn($middlewareStackMock);

        $middlewareStackMock->expects($this->once())
            ->method('compile')
            ->with($request)
            ->willReturn($compiledRequestHandler);

        $sut->onKernelController($event);

        $this->assertEquals($compiledRequestHandler, $event->getController());
    }

    public function testThatListenerIsSubscribedToEvents()
    {
        $this->assertArrayHasKey(KernelEvents::CONTROLLER, MiddlewareStackInjectorListener::getSubscribedEvents());
    }
}
