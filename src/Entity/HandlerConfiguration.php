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

namespace NoGlitchYo\MiddlewareBundle\Entity;

use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;

class HandlerConfiguration implements HandlerConfigurationInterface
{
    /**
     * @var MiddlewareCollectionInterface
     */
    private $collection;

    /**
     * @var HandlerCondition[]
     */
    private $conditions = [];

    public function setCollection(MiddlewareCollectionInterface $collection): HandlerConfigurationInterface
    {
        $this->collection = $collection;

        return $this;
    }

    public function getCollection(): MiddlewareCollectionInterface
    {
        return $this->collection;
    }

    public function addCondition(HandlerCondition $filter): HandlerConfigurationInterface
    {
        $this->conditions[] = $filter;

        return $this;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }
}
