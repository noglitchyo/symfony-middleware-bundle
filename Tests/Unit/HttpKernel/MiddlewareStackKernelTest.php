<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Tests\Unit\HttpKernel;

use Exception;
use NoGlitchYo\MiddlewareBundle\Exception\NotImplementedException;
use NoGlitchYo\MiddlewareBundle\HttpKernel\MiddlewareStackKernel;
use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStackInterface;
use Nyholm\Psr7\ServerRequest as PsrServerRequest;
use Nyholm\Psr7\Response as PsrResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class MiddlewareStackKernelTest extends TestCase
{
    /**
     * @var MiddlewareStackInterface|MockObject
     */
    private $middlewareStackMock;

    /**
     * @var MockObject|HttpFoundationFactoryInterface
     */
    private $httpFoundationFactoryMock;

    /**
     * @var MockObject|HttpKernelInterface
     */
    private $httpKernelMock;

    /**
     * @var MockObject|HttpMessageFactoryInterface
     */
    private $psrHttpFactoryMock;

    /**
     * @var MiddlewareStackKernel
     */
    private $sut;

    protected function setUp(): void
    {
        $this->httpFoundationFactoryMock = $this->createMock(HttpFoundationFactoryInterface::class);
        $this->psrHttpFactoryMock        = $this->createMock(HttpMessageFactoryInterface::class);
        $this->middlewareStackMock       = $this->createMock(MiddlewareStackInterface::class);
        $this->httpKernelMock            = $this->createMock(HttpKernelInterface::class);

        $this->sut = new MiddlewareStackKernel(
            $this->httpFoundationFactoryMock,
            $this->psrHttpFactoryMock,
            $this->middlewareStackMock,
            $this->httpKernelMock
        );
    }

    public function testHandleAddKernelAsDefaultHandlerAndReturnResponseFromStackHandler(): void
    {
        $response         = new PsrResponse();
        $expectedResponse = new Response();
        $requestHandler   = $this->getRequestHandler($response);
        $request          = new Request();
        $psrServerRequest = new PsrServerRequest('POST', '/test/uri');

        $this->middlewareStackMock->expects($this->once())
            ->method('withDefaultHandler')
            ->willReturn($this->middlewareStackMock);

        $this->middlewareStackMock->expects($this->once())
            ->method('compile')
            ->with($request)
            ->willReturn($requestHandler);

        $this->psrHttpFactoryMock->expects($this->once())
            ->method('createRequest')
            ->with($request)
            ->willReturn($psrServerRequest);

        $this->httpFoundationFactoryMock->expects($this->once())
            ->method('createResponse')
            ->with($response)
            ->willReturn($expectedResponse);

        $this->assertSame($expectedResponse, $this->sut->handle($request));
    }

    public function testHandleCatchExceptionAndThrowNewExceptionIfCatchIsTrueAndExceptionIsRaised(): void
    {
        $request          = new Request();
        $exception        = new Exception();

        $this->middlewareStackMock->expects($this->once())
            ->method('withDefaultHandler')
            ->willThrowException($exception);

        $this->expectException(NotImplementedException::class);

        $this->sut->handle($request);
    }

    public function testHandleCatchExceptionAndThrowSameExceptionIfCatchIsFalseAndExceptionIsRaised(): void
    {
        $request          = new Request();
        $exception        = new Exception();

        $this->middlewareStackMock->expects($this->once())
            ->method('withDefaultHandler')
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $this->sut->handle($request, HttpKernelInterface::MASTER_REQUEST, false);
    }

    private function getRequestHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new class($response) implements RequestHandlerInterface
        {
            /**
             * @var Response
             */
            private $response;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function __invoke(ServerRequestInterface $request): ResponseInterface
            {
                return $this->handle($request);
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }
}
