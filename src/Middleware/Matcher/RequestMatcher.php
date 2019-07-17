<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Middleware\Matcher;

use NoGlitchYo\MiddlewareBundle\Entity\MiddlewareStackEntry;
use Symfony\Component\HttpFoundation\Request;

class RequestMatcher implements RequestMatcherInterface
{
    public function match(Request $request, MiddlewareStackEntry $middlewareStackEntry): bool
    {
        if ($middlewareStackEntry->getCondition() === null) {
            return true;
        }

        $routeName = $request->get('_route');
        $routePath = $request->getRequestUri();

        $isTrue = true;
        if ($middlewareStackEntry->getCondition()->getRouteName() !== null) {
            $isTrue = $isTrue && $routeName === $middlewareStackEntry->getCondition()->getRouteName();
        }
        if ($middlewareStackEntry->getCondition()->getRoutePath() !== null) {
            $isTrue = $isTrue && $routePath === $middlewareStackEntry->getCondition()->getRoutePath();
        }

        return $isTrue;
    }
}