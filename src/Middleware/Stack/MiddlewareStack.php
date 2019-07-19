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
use NoGlitchYo\MiddlewareBundle\Entity\MiddlewareStackEntry;
use NoGlitchYo\MiddlewareBundle\Factory\MiddlewareCollectionFactory;
use NoGlitchYo\MiddlewareBundle\Middleware\Matcher\RequestMatcherInterface;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;
use NoGlitchYo\MiddlewareCollectionRequestHandler\RequestHandler;
use NoGlitchYo\MiddlewareCollectionRequestHandler\RequestHandlerTrait;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class MiddlewareStack implements MiddlewareStackInterface
{
    use RequestHandlerTrait;

    /**
     * @var MiddlewareStackEntry[]
     */
    private $middlewareStackEntries = [];

    /**
     * @var MiddlewareCollectionFactory
     */
    private $middlewareCollectionFactory;

    /**
     * @var RequestHandlerInterface|null
     */
    private $defaultHandler = null;
    /**
     * @var RequestMatcherInterface
     */
    private $matcher;

    public function __construct(
        MiddlewareCollectionFactory $middlewareCollectionFactory,
        RequestMatcherInterface $matcher
    ) {
        $this->middlewareCollectionFactory = $middlewareCollectionFactory;
        $this->matcher                     = $matcher;
    }

    public function add(
        MiddlewareCollectionInterface $collection,
        HandlerCondition $condition = null
    ): MiddlewareStackInterface {
        $this->middlewareStackEntries[] = new MiddlewareStackEntry($collection, $condition);

        return $this;
    }

    public function withDefaultHandler($defaultHandler): MiddlewareStackInterface
    {
        if (!$defaultHandler instanceof RequestHandlerInterface) {
            if (!is_callable($defaultHandler)) {
                throw new InvalidArgumentException(
                    '$defaultHandler must be a callable or implements ' . RequestHandlerInterface::class
                );
            }
            $defaultHandler = static::createRequestHandlerFromCallable($defaultHandler);
        }

        $this->defaultHandler = $defaultHandler;

        return $this;
    }

    public function compile(Request $request): RequestHandlerInterface
    {
        $finalMiddlewareCollection = $this->middlewareCollectionFactory->create();

        foreach ($this->middlewareStackEntries as $stackEntry) {
            if ($this->matcher->match($request, $stackEntry)) {
                $finalMiddlewareCollection->add(new RequestHandler($stackEntry->getCollection()));
            }
        }

        return new RequestHandler($finalMiddlewareCollection, $this->defaultHandler);
    }
}
