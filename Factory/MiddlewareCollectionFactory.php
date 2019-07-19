<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Factory;

use NoGlitchYo\MiddlewareCollectionRequestHandler\Collection\SplStackMiddlewareCollection;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;

class MiddlewareCollectionFactory
{
    public function create(): MiddlewareCollectionInterface
    {
        return new SplStackMiddlewareCollection();
    }
}
