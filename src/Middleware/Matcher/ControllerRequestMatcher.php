<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Middleware\Matcher;

use NoGlitchYo\MiddlewareBundle\Entity\MiddlewareStackEntry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @codeCoverageIgnore
 */
class ControllerRequestMatcher implements RequestMatcherInterface
{
    public function match(Request $request, MiddlewareStackEntry $middlewareStackEntry): bool
    {
        // TODO: Implement match() method.
    }
}