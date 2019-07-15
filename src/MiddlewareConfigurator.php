<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle;

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

            if (!empty($handlerConfiguration->getFilters())) {
                $filters = $handlerConfiguration->getFilters();
                foreach ($filters as $filter) {
                    if ($filter->getRouteName()) {
                        $middlewareDispatcher->onRouteNameRun(
                            $filter->getRoutePath(),
                            $middlewareCollection
                        );
                    }
                    if ($filter->getRoutePath()) {
                        $middlewareDispatcher->onRoutePathRun(
                            $filter->getRoutePath(),
                            $middlewareCollection
                        );
                    }
                    if ($filter->getController()) {
                        $middlewareDispatcher->onHandlerRun(
                            $filter->getController(),
                            $middlewareCollection
                        );
                    }
                }
            } else {
                $middlewareDispatcher->alwaysRun($middlewareCollection);
            }
        }
    }
}
