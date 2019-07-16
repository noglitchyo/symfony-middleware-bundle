<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Middleware\Stack;

use NoGlitchYo\MiddlewareBundle\Entity\HandlerCondition;
use NoGlitchYo\MiddlewareBundle\Entity\MiddlewareStackEntry;
use NoGlitchYo\MiddlewareBundle\Factory\MiddlewareCollectionFactory;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;
use NoGlitchYo\MiddlewareCollectionRequestHandler\RequestHandler;
use Symfony\Component\HttpFoundation\Request;

class MiddlewareStack implements MiddlewareStackInterface
{
    /**
     * @var MiddlewareStackEntry[]
     */
    private $middlewareStackEntries;

    /**
     * @var MiddlewareCollectionFactory
     */
    private $middlewareCollectionFactory;

    public function __construct(MiddlewareCollectionFactory $middlewareCollectionFactory)
    {
        $this->middlewareCollectionFactory = $middlewareCollectionFactory;
    }

    public function getRequestHandler(Request $request, ?callable $defaultHandler = null): RequestHandler
    {
        $finalMiddlewareCollection = $this->middlewareCollectionFactory->create();

        foreach ($this->middlewareStackEntries as $stackEntry) {
            if ($this->matchRequest($stackEntry, $request)) {
                $finalMiddlewareCollection->add(new RequestHandler($stackEntry->getCollection()));
            }
        }

        return RequestHandler::fromCallable($defaultHandler, $finalMiddlewareCollection);
    }

    public function add(
        MiddlewareCollectionInterface $collection,
        HandlerCondition $condition = null
    ): MiddlewareStackEntry
    {
        $middlewareStackEntry = new MiddlewareStackEntry($collection, $condition);

        $this->middlewareStackEntries[] = $middlewareStackEntry;

        return $middlewareStackEntry;
    }

    private function matchRequest(MiddlewareStackEntry $stackEntry, Request $request): bool
    {
        if ($stackEntry->getCondition() === null) {
            return true;
        }

        $routeName = $request->get('_route');
        $routePath = $request->getRequestUri();

        $isTrue = true;
        if ($stackEntry->getCondition()->getRouteName() !== null) {
            $isTrue = $isTrue && $routeName === $stackEntry->getCondition()->getRouteName();
        }
        if ($stackEntry->getCondition()->getRoutePath() !== null) {
            $isTrue = $isTrue && $routePath === $stackEntry->getCondition()->getRoutePath();
        }

        return $isTrue;
    }
}
