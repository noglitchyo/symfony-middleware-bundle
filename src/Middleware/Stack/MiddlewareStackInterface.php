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

namespace NoGlitchYo\MiddlewareBundle\Middleware\Stack;

use InvalidArgumentException;
use NoGlitchYo\MiddlewareBundle\Entity\HandlerCondition;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Aggregate middleware collections and handler conditions to execute the collections.
 * Compile and return a RequestHandler wrapping the collections to execute for which conditions were satisfied.
 */
interface MiddlewareStackInterface
{
    /**
     * Default handler to provide response if no middleware was able to produce one.
     *
     * @param callable|RequestHandlerInterface $requestHandler
     * @return MiddlewareStackInterface
     *
     * @throws InvalidArgumentException
     */
    public function withDefaultHandler($requestHandler): self;

    /**
     * Return a compiled request handler wrapping the collections of middleware to execute.
     *
     * @param Request $request
     * @return RequestHandlerInterface
     */
    public function compile(Request $request): RequestHandlerInterface;

    /**
     * Add to the stack a middleware collection to run
     *
     * @param MiddlewareCollectionInterface $collection
     * @param HandlerCondition|null $condition
     * @return MiddlewareStackInterface
     */
    public function add(
        MiddlewareCollectionInterface $collection,
        HandlerCondition $condition = null
    ): MiddlewareStackInterface;
}
