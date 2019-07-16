<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle;

use InvalidArgumentException;
use NoGlitchYo\MiddlewareBundle\Exception\UndefinedMiddlewareCollectionException;
use NoGlitchYo\MiddlewareBundle\Factory\MiddlewareCollectionFactory;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;
use NoGlitchYo\MiddlewareCollectionRequestHandler\RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    private const ON_HANDLER_INDEX = 'handlers';
    private const ON_ROUTE_NAME_INDEX = 'routeNames';
    private const ON_ROUTE_PATH_INDEX = 'routePaths';
    private const ALWAYS_RUN_INDEX = '*';

    /**
     * @var MiddlewareCollectionInterface[]
     */
    private $middlewareCollections;
    /**
     * @var MiddlewareCollectionFactory
     */
    private $middlewareCollectionFactory;

    public function __construct(MiddlewareCollectionFactory $middlewareCollectionFactory)
    {
        $this->middlewareCollectionFactory = $middlewareCollectionFactory;
    }

    /**
     * Middleware collection run order depends on the returned middleware collection factory
     * TODO: we should be able to define priority for collection execution, like runBefore/runAfter/runLast/runFirst
     * @param callable $controller
     * @return callable
     */
    public function dispatch(callable $controller, Request $request): callable
    {
        $finalMiddlewareCollection = $this->middlewareCollectionFactory->create();

        // run the middleware collections who must always be run
        foreach ($this->getAlwaysRunCollections() as $middlewareCollection) {
            $finalMiddlewareCollection->add(new RequestHandler($middlewareCollection));
        }

        // run collections only for controller
        if ($this->hasControllerRequestHandler($controller)) {
            $finalMiddlewareCollection->add($this->getControllerRequestHandler($controller));
        }

        $routeName = $request->get('_route');
        if ($this->hasRouteNameRequestHandler($routeName)) {
            $finalMiddlewareCollection->add($this->getRouteNameRequestHandler($routeName));
        }

        $routePath = $request->getRequestUri();
        if ($this->hasRoutePathRequestHandler($routePath)) {
            $finalMiddlewareCollection->add($this->getRoutePathRequestHandler($routePath));
        }

        return RequestHandler::fromCallable($controller, $finalMiddlewareCollection);
    }

    /**
     * Always run the given middleware collection
     *
     * @param MiddlewareCollectionInterface $collection
     * @return MiddlewareCollectionInterface
     */
    public function alwaysRun(MiddlewareCollectionInterface $collection): MiddlewareCollectionInterface
    {
        $this->middlewareCollections[self::ALWAYS_RUN_INDEX][] = $collection;

        return $collection;
    }

    /**
     * Run the given middleware collection when the current route match the given route path
     *
     * @param string $routePath
     * @param MiddlewareCollectionInterface $collection
     * @return MiddlewareCollectionInterface
     */
    public function onRoutePathRun(
        string $routePath,
        MiddlewareCollectionInterface $collection
    ): MiddlewareCollectionInterface
    {
        $this->middlewareCollections[self::ON_ROUTE_PATH_INDEX][$routePath] = $collection;

        return $this->middlewareCollections[self::ON_ROUTE_PATH_INDEX][$routePath];
    }

    /**
     * Run the given middleware collection when the current route match the given route name
     *
     * @param string $routeName
     * @param MiddlewareCollectionInterface $collection
     * @return MiddlewareCollectionInterface
     */
    public function onRouteNameRun(
        string $routeName,
        MiddlewareCollectionInterface $collection
    ): MiddlewareCollectionInterface
    {
        $this->middlewareCollections[self::ON_ROUTE_NAME_INDEX][$routeName] = $collection;

        return $this->middlewareCollections[self::ON_ROUTE_NAME_INDEX][$routeName];
    }

    /**
     * Run the given middleware collection when the current request handler match the given handler class name
     *
     * @param string $handler
     * @param MiddlewareCollectionInterface $collection
     * @return MiddlewareCollectionInterface
     */
    public function onHandlerRun(
        string $handler,
        MiddlewareCollectionInterface $collection
    ): MiddlewareCollectionInterface
    {
        if (!is_subclass_of($handler, RequestHandlerInterface::class, true)) {
            throw new InvalidArgumentException('$handler must be an instance of ' . RequestHandlerInterface::class);
        }

        $identifier = $this->getControllerRequestHandlerIdentifier($handler);

        $this->middlewareCollections[self::ON_HANDLER_INDEX][$identifier] = $collection;

        return $this->middlewareCollections[self::ON_HANDLER_INDEX][$identifier];
    }

    private function hasControllerRequestHandler($handler): bool
    {
        $identifier = $this->getControllerRequestHandlerIdentifier($handler);

        return isset($this->middlewareCollections[self::ON_HANDLER_INDEX][$identifier]);
    }

    private function hasRouteNameRequestHandler(string $routeName): bool
    {
        return isset($this->middlewareCollections[self::ON_ROUTE_NAME_INDEX][$routeName]);
    }

    private function hasRoutePathRequestHandler(string $routeName): bool
    {
        return isset($this->middlewareCollections[self::ON_ROUTE_PATH_INDEX][$routeName]);
    }

    private function getOnHandlerRunCollection($handler): MiddlewareCollectionInterface
    {
        if (!$this->hasControllerRequestHandler($handler)) {
            throw new UndefinedMiddlewareCollectionException();
        }

        $identifier = $this->getControllerRequestHandlerIdentifier($handler);

        return $this->middlewareCollections[self::ON_HANDLER_INDEX][$identifier];
    }

    private function getOnRouteNameRunCollection(string $name): MiddlewareCollectionInterface
    {
        if (!$this->hasRouteNameRequestHandler($name)) {
            throw new UndefinedMiddlewareCollectionException();
        }

        return $this->middlewareCollections[self::ON_ROUTE_NAME_INDEX][$name];
    }

    private function getOnRoutePathRunCollection(string $name): MiddlewareCollectionInterface
    {
        if (!$this->hasRoutePathRequestHandler($name)) {
            throw new UndefinedMiddlewareCollectionException();
        }

        return $this->middlewareCollections[self::ON_ROUTE_PATH_INDEX][$name];
    }

    private function getAlwaysRunCollections(): array
    {
        return $this->middlewareCollections[self::ALWAYS_RUN_INDEX] ?? [];
    }

    private function getControllerRequestHandler(callable $controller): MiddlewareInterface
    {
        return new RequestHandler($this->getOnHandlerRunCollection($controller));
    }

    private function getRouteNameRequestHandler(string $routeName): MiddlewareInterface
    {
        return new RequestHandler($this->getOnRouteNameRunCollection($routeName));
    }

    private function getRoutePathRequestHandler(string $routeName): MiddlewareInterface
    {
        return new RequestHandler($this->getOnRoutePathRunCollection($routeName));
    }

    private function getControllerRequestHandlerIdentifier($handler): string
    {
        return is_string($handler) ? $handler : get_class($handler);
    }
}
