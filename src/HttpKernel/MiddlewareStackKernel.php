<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\HttpKernel;

use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStackInterface;
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

    public function __construct(
        HttpFoundationFactory $httpFoundationFactory,
        PsrHttpFactory $psrHttpFactory,
        MiddlewareStackInterface $middlewareStackHandler
    )
    {
        $this->httpFoundationFactory  = $httpFoundationFactory;
        $this->psrHttpFactory         = $psrHttpFactory;
        $this->middlewareStackHandler = $middlewareStackHandler;
    }

    /**
     * Middleware collection run order depends on the returned middleware collection factory
     * TODO: we should be able to define priority for collection execution, like runBefore/runAfter/runLast/runFirst*
     *
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true): Response
    {
        try {
            $requestHandler = $this->middlewareStackHandler->getRequestHandler($request);

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
}
