<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle;

use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;

class MiddlewareConfigurator
{
    /**
     * @var HandlerConfigurationInterface[]
     */
    private $handlerConfigurations;

    public function __construct(iterable $handlers)
    {
        $this->handlerConfigurations = $handlers;
    }

    public function __invoke(MiddlewareDispatcher $middlewareDispatcher)
    {
        foreach ($this->handlerConfigurations as $handlerConfiguration) {
            $middlewareCollection = $handlerConfiguration->getCollection();


            if ($handlerConfiguration->getFilter()) {
                $filter = $handlerConfiguration->getFilter();
                if ($filter->getRouteName()) {
                    $middlewareDispatcher->onRoute(
                        $filter->getRoutePath(),
                        $middlewareCollection
                    );
                }
                if ($filter->getRoutePath()) {
                    $middlewareDispatcher->onPath(
                        $filter->getRoutePath(),
                        $middlewareCollection
                    );
                }
                if ($filter->getController()) {
                    $middlewareDispatcher->onHandler(
                        $filter->getController(),
                        $middlewareCollection
                    );
                }
            } else {
                $middlewareDispatcher->always($middlewareCollection);
            }
        }
    }
}
