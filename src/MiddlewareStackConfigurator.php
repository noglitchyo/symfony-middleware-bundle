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

namespace NoGlitchYo\MiddlewareBundle;

use InvalidArgumentException;
use NoGlitchYo\MiddlewareBundle\Entity\HandlerConfigurationInterface;
use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStackInterface;

class MiddlewareStackConfigurator
{
    /**
     * @var iterable|HandlerConfigurationInterface[]
     */
    private $handlerConfigurations;

    public function __construct(iterable $handlers)
    {
        $this->handlerConfigurations = $handlers;
    }

    public function __invoke(MiddlewareStackInterface $middlewareStack): void
    {
        foreach ($this->handlerConfigurations as $handlerConfiguration) {
            if (!$handlerConfiguration instanceof HandlerConfigurationInterface) {
                throw new InvalidArgumentException(
                    'Handler must be an instance of ' . HandlerConfigurationInterface::class
                );
            }

            $middlewareCollection = $handlerConfiguration->getCollection();

            if (!empty($handlerConfiguration->getConditions())) {
                $conditions = $handlerConfiguration->getConditions();
                foreach ($conditions as $condition) {
                    $middlewareStack->add($middlewareCollection, $condition);
                }
            } else {
                $middlewareStack->add($middlewareCollection);
            }
        }
    }
}
