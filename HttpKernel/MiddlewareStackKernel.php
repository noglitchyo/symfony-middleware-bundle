<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\HttpKernel;

use NoGlitchYo\MiddlewareBundle\Exception\NotImplementedException;
use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStackInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

class MiddlewareStackKernel implements HttpKernelInterface
{
    /**
     * @var HttpFoundationFactoryInterface
     */
    private $httpFoundationFactory;
    /**
     * @var HttpMessageFactoryInterface
     */
    private $psrHttpFactory;
    /**
     * @var MiddlewareStackInterface
     */
    private $middlewareStack;
    /**
     * @var HttpKernelInterface
     */
    private $httpKernel;

    public function __construct(
        HttpFoundationFactoryInterface $httpFoundationFactory,
        HttpMessageFactoryInterface $psrHttpFactory,
        MiddlewareStackInterface $middlewareStack,
        HttpKernelInterface $httpKernel
    ) {
        $this->httpFoundationFactory = $httpFoundationFactory;
        $this->psrHttpFactory        = $psrHttpFactory;
        $this->middlewareStack       = $middlewareStack;
        $this->httpKernel            = $httpKernel;
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true): Response
    {
        try {
            $requestHandler = $this->middlewareStack
                ->withDefaultHandler($this->getHttpKernelAsRequestHandler())
                ->compile($request);

            $response = $requestHandler->handle($this->psrHttpFactory->createRequest($request));
        } catch (Throwable $t) {
            if ($catch === true) {
                throw new NotImplementedException($t);
            } else {
                throw $t;
            }
        }

        return $this->httpFoundationFactory->createResponse($response);
    }

    private function getHttpKernelAsRequestHandler()
    {
        return new class($this->httpKernel, $this->httpFoundationFactory, $this->psrHttpFactory) implements RequestHandlerInterface
        {
            /**
             * @var HttpKernelInterface
             */
            private $httpKernel;
            /**
             * @var HttpFoundationFactory
             */
            private $foundationFactory;
            /**
             * @var PsrHttpFactory
             */
            private $psrHttpFactory;

            public function __construct(
                HttpKernelInterface $httpKernel,
                HttpFoundationFactoryInterface $foundationFactory,
                HttpMessageFactoryInterface $psrHttpFactory
            ) {
                $this->httpKernel        = $httpKernel;
                $this->foundationFactory = $foundationFactory;
                $this->psrHttpFactory    = $psrHttpFactory;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $foundationRequest = $this->foundationFactory->createRequest($request);

                $response = $this->httpKernel->handle($foundationRequest);

                return $this->psrHttpFactory->createResponse($response);
            }
        };
    }
}
