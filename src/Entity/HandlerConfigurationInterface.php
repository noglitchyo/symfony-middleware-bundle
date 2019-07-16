<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Entity;

use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;

interface HandlerConfigurationInterface
{
    public function setIdentifier(string $identifier): self;

    public function getIdentifier(): string;

    public function setCollection(MiddlewareCollectionInterface $collection): self;

    public function getCollection(): MiddlewareCollectionInterface;

    public function addCondition(HandlerCondition $filter): self;

    /**
     * @return HandlerCondition[]
     */
    public function getConditions(): array;
}