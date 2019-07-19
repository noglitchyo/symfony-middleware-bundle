<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Entity;

use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;

class HandlerConfiguration implements HandlerConfigurationInterface
{
    /**
     * @var MiddlewareCollectionInterface
     */
    private $collection;

    /**
     * @var HandlerCondition[]
     */
    private $conditions = [];

    public function setCollection(MiddlewareCollectionInterface $collection): HandlerConfigurationInterface
    {
        $this->collection = $collection;

        return $this;
    }

    public function getCollection(): MiddlewareCollectionInterface
    {
        return $this->collection;
    }

    public function addCondition(HandlerCondition $filter): HandlerConfigurationInterface
    {
        $this->conditions[] = $filter;

        return $this;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }
}
