<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Entity;

use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;

class MiddlewareStackEntry
{
    /**
     * @var MiddlewareCollectionInterface
     */
    private $collection;

    /**
     * @var HandlerCondition|null
     */
    private $condition;

    public function __construct(
        MiddlewareCollectionInterface $middlewareCollection,
        ?HandlerCondition $condition = null
    ) {
        $this->collection = $middlewareCollection;
        $this->condition  = $condition;
    }

    public function getCollection(): MiddlewareCollectionInterface
    {
        return $this->collection;
    }

    public function getCondition(): ?HandlerCondition
    {
        return $this->condition;
    }
}
