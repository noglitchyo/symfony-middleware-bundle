<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle;

use InvalidArgumentException;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;
use NoGlitchYo\MiddlewareCollectionRequestHandler\RequestHandler;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareDispatcher
{
    /**
     * @var MiddlewareCollectionInterface[]
     */
    private $middlewareCollections;

    public function dispatchController($controller): callable
    {
        if ($this->hasHandler($controller)) {

            return RequestHandler::fromCallable($controller, $this->getHandler($controller));
        }


    }

    public function always(MiddlewareCollectionInterface $collection = null): MiddlewareCollectionInterface
    {
        if ($collection === null) {
            $collection = $this->middlewareCollectionFactory->create();
        }

        // TODO: it needs to be merged
        $this->middlewareCollections['*'][] = $collection;

        return $collection;
    }

    public function onRoute(
        string $routeName,
        MiddlewareCollectionInterface $collection = null
    ): MiddlewareCollectionInterface {
        if ($collection === null) {
            $collection = $this->middlewareCollectionFactory->create();
        }

        $this->middlewareCollections['routes'][$routeName] = $collection;

        return $this->middlewareCollections['routes'][$routeName];
    }

    public function onHandler(
        string $handler,
        MiddlewareCollectionInterface $collection = null
    ): MiddlewareCollectionInterface {
        if (!is_subclass_of($handler, RequestHandlerInterface::class, true)) {
            throw new InvalidArgumentException('$handler must be an instance of ' . RequestHandlerInterface::class);
        }

        if ($collection === null) {
            $collection = $this->middlewareCollectionFactory->create();
        }

        $identifier = is_string($handler) ? $handler : get_class($handler);

        $this->middlewareCollections['handlers'][$identifier] = $collection;

        return $this->middlewareCollections['handlers'][$identifier];
    }

    public function hasHandler($handler): bool
    {
        $identifier = is_string($handler) ? $handler : get_class($handler);

        return isset($this->middlewareCollections['handlers'][$identifier]);
    }

    public function getHandler($handler)
    {
        $identifier = is_string($handler) ? $handler : get_class($handler);

        return $this->middlewareCollections['handlers'][$identifier];
    }
}
