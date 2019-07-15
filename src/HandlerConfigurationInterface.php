<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle;

use NoGlitchYo\MiddlewareBundle\Entity\HandlerConfiguration\Filter;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;

interface HandlerConfigurationInterface
{
    public function setIdentifier(string $identifier): self;

    public function getIdentifier(): string;

    public function setCollection(MiddlewareCollectionInterface $collection): self;

    public function getCollection(): MiddlewareCollectionInterface;

    public function setFilter(Filter $filter): self;

    public function getFilter(): Filter;
}