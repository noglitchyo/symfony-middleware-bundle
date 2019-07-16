<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle;

use Symfony\Component\HttpFoundation\Request;

interface MiddlewareDispatcherInterface
{
    public function dispatch(callable $controller, Request $request): callable;
}