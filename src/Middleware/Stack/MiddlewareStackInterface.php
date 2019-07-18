<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Middleware\Stack;

use InvalidArgumentException;
use NoGlitchYo\MiddlewareBundle\Entity\HandlerCondition;
use NoGlitchYo\MiddlewareBundle\Entity\MiddlewareStackEntry;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;
use NoGlitchYo\MiddlewareCollectionRequestHandler\RequestHandler;
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
     * @return RequestHandler|callable
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