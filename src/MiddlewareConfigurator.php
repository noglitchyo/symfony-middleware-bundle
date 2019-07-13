<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle;

use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;

class MiddlewareConfigurator
{
    /**
     * @var MiddlewareCollectionInterface[]
     */
    private $middlewareCollections;

    /**
     * @var array
     */
    private $handlers;

    public function __construct(iterable $middlewareCollections, array $handlers)
    {
        $this->handlers              = $handlers;
        foreach ($middlewareCollections as $collection) {
            $this->middlewareCollections[] = $collection;
        }
    }

    public function __invoke(MiddlewareDispatcher $middlewareDispatcher)
    {
        foreach ($this->handlers as $handlerConfiguration) {
//            $collectionIdentifier = $handlerConfiguration['collection'];
//            $middlewareCollection = $this->middlewareCollections[$collectionIdentifier];
            $middlewareCollection = $this->middlewareCollections[0];


            if (isset($handlerConfiguration['filter'])) {
                $filter = $handlerConfiguration['filter'];
                //                        if (isset($filter['routeName'])) {
                //                            $this->middlewareConfigurator->onRoute();
                //                        }
                if (isset($filter['routePath'])) {
                    $middlewareDispatcher->onRoute(
                        $filter['routePath'],
                        $middlewareCollection
                    );
                }
                if (isset($filter['controller'])) {
                    $middlewareDispatcher->onHandler(
                        $filter['controller'],
                        $middlewareCollection
                    );
                }
            } else {
                $middlewareDispatcher->always($middlewareCollection);
            }
        }

    }
}
