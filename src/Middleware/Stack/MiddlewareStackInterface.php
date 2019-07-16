<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Middleware\Stack;

use NoGlitchYo\MiddlewareBundle\Entity\HandlerCondition;
use NoGlitchYo\MiddlewareBundle\Entity\MiddlewareStackEntry;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;
use NoGlitchYo\MiddlewareCollectionRequestHandler\RequestHandler;
use Symfony\Component\HttpFoundation\Request;

interface MiddlewareStackInterface
{
    /**
     * Retrieve compiled request handler
     * @param Request $request
     * @param callable|null $defaultHandler
     * @return RequestHandler|callable
     */
    public function getRequestHandler(Request $request, ?callable $defaultHandler = null): RequestHandler;

    /**
     * Add to the stack a middleware collection to run
     *
     * @param MiddlewareCollectionInterface $collection
     * @param HandlerCondition|null $condition
     * @return MiddlewareStackEntry
     */
    public function add(
        MiddlewareCollectionInterface $collection,
        HandlerCondition $condition = null
    ): MiddlewareStackEntry;
}