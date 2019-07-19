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

class HandlerCondition
{
    /**
     * @var string|null
     */
    private $routeName;

    /**
     * @var string|null
     */
    private $routePath;

    /**
     * @var string|null
     */
    private $controller;

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(string $routeName = null): self
    {
        $this->routeName = $routeName;

        return $this;
    }

    public function getRoutePath(): ?string
    {
        return $this->routePath;
    }

    public function setRoutePath(string $routePath = null): self
    {
        $this->routePath = $routePath;

        return $this;
    }

//    public function getController(): ?string
//    {
//        return $this->controller;
//    }

//    public function setController(string $controller = null): self
//    {
//        $this->controller = $controller;
//
//        return $this;
//    }
}
