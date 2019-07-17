<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Middleware\Matcher;

use NoGlitchYo\MiddlewareBundle\Entity\MiddlewareStackEntry;
use Symfony\Component\HttpFoundation\Request;

interface RequestMatcherInterface
{
    public function match(Request $request, MiddlewareStackEntry $middlewareStackEntry): bool;
}