<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Middleware\Stack;

use InvalidArgumentException;
use NoGlitchYo\MiddlewareBundle\Entity\HandlerCondition;
use NoGlitchYo\MiddlewareBundle\Entity\MiddlewareStackEntry;
use NoGlitchYo\MiddlewareBundle\Factory\MiddlewareCollectionFactory;
use NoGlitchYo\MiddlewareBundle\Middleware\Matcher\RequestMatcherInterface;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;
use NoGlitchYo\MiddlewareCollectionRequestHandler\RequestHandler;
use NoGlitchYo\MiddlewareCollectionRequestHandler\RequestHandlerTrait;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class MiddlewareStack implements MiddlewareStackInterface
{
    use RequestHandlerTrait;

    /**
     * @var MiddlewareStackEntry[]
     */
    private $middlewareStackEntries = [];

    /**
     * @var MiddlewareCollectionFactory
     */
    private $middlewareCollectionFactory;

    /**
     * @var RequestHandlerInterface|null
     */
    private $defaultHandler = null;
    /**
     * @var RequestMatcherInterface
     */
    private $matcher;

    public function __construct(
        MiddlewareCollectionFactory $middlewareCollectionFactory,
        RequestMatcherInterface $matcher
    ) {
        $this->middlewareCollectionFactory = $middlewareCollectionFactory;
        $this->matcher                     = $matcher;
    }

    public function add(
        MiddlewareCollectionInterface $collection,
        HandlerCondition $condition = null
    ): MiddlewareStackInterface {
        $this->middlewareStackEntries[] = new MiddlewareStackEntry($collection, $condition);

        return $this;
    }

    public function withDefaultHandler($defaultHandler): MiddlewareStackInterface
    {
        if (!$defaultHandler instanceof RequestHandlerInterface) {
            if (!is_callable($defaultHandler)) {
                throw new InvalidArgumentException(
                    '$defaultHandler must be a callable or implements ' . RequestHandlerInterface::class
                );
            }
            $defaultHandler = static::createRequestHandlerFromCallable($defaultHandler);
        }

        $this->defaultHandler = $defaultHandler;

        return $this;
    }

    public function compile(Request $request): RequestHandlerInterface
    {
        $finalMiddlewareCollection = $this->middlewareCollectionFactory->create();

        foreach ($this->middlewareStackEntries as $stackEntry) {
            if ($this->matcher->match($request, $stackEntry)) {
                $finalMiddlewareCollection->add(new RequestHandler($stackEntry->getCollection()));
            }
        }

        return new RequestHandler($finalMiddlewareCollection, $this->defaultHandler);
    }
}
