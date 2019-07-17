<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\HttpKernel;

use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStackInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

class MiddlewareStackKernel implements HttpKernelInterface
{
    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;
    /**
     * @var PsrHttpFactory
     */
    private $psrHttpFactory;
    /**
     * @var MiddlewareStackInterface
     */
    private $middlewareStackHandler;
    /**
     * @var HttpKernelInterface
     */
    private $httpKernel;

    public function __construct(
        HttpFoundationFactory $httpFoundationFactory,
        PsrHttpFactory $psrHttpFactory,
        MiddlewareStackInterface $middlewareStackHandler,
        HttpKernelInterface $httpKernel
    )
    {
        $this->httpFoundationFactory  = $httpFoundationFactory;
        $this->psrHttpFactory         = $psrHttpFactory;
        $this->middlewareStackHandler = $middlewareStackHandler;
        $this->httpKernel             = $httpKernel;
    }

    /**
     * Middleware collection run order depends on the returned middleware collection factory
     * TODO: we should be able to define priority for collection execution, like runBefore/runAfter/runLast/runFirst*
     *
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true): Response
    {
        try {
            $requestHandler = $this->middlewareStackHandler
                ->withDefaultHandler($this->getHttpKernelAsRequestHandler())
                ->compile($request);

            $response = $requestHandler->handle($this->psrHttpFactory->createRequest($request));
        } catch (Throwable $t) {
            if ($catch === true) {
                throw new \Exception('Producing response from response is not implemented yet', 0, $t);
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
                HttpFoundationFactory $foundationFactory,
                PsrHttpFactory $psrHttpFactory
            )
            {
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
