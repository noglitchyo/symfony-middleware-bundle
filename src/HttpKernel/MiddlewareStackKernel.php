<?php declare(strict_types=1);
/**
 * MIT License
 *
 * Copyright (c) 2019 Maxime Elomari
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace NoGlitchYo\MiddlewareBundle\HttpKernel;

use NoGlitchYo\MiddlewareBundle\Exception\NotImplementedException;
use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStackInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
        return new class($this->httpKernel, $this->httpFoundationFactory, $this->psrHttpFactory)
            implements RequestHandlerInterface
        {
            /**
             * @var HttpKernelInterface
             */
            private $httpKernel;
            /**
             * @var HttpFoundationFactoryInterface
             */
            private $foundationFactory;
            /**
             * @var HttpMessageFactoryInterface
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
