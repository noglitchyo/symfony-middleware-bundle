<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Entity;

use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;

class HandlerConfiguration implements HandlerConfigurationInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var MiddlewareCollectionInterface
     */
    private $collection;

    /**
     * @var HandlerCondition[]
     */
    private $conditions = [];

    public function setIdentifier(string $identifier): HandlerConfigurationInterface
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

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