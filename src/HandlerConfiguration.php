<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle;

use NoGlitchYo\MiddlewareBundle\Entity\HandlerConfiguration\Filter;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;

class HandlerConfiguration implements HandlerConfigurationInterface{

    /**
     * @var string
     */
    private $identifier;
    /**
     * @var MiddlewareCollectionInterface
     */
    private $collection;
    /**
     * @var Filter
     */
    private $filter;

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

    public function setFilter(Filter $filter): HandlerConfigurationInterface
    {
        $this->filter = $filter;

        return $this;
    }

    public function getFilter(): Filter
    {
        return $this->filter;
    }
};