<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Tests\Unit\Factory;

use NoGlitchYo\MiddlewareBundle\Factory\MiddlewareCollectionFactory;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;
use PHPUnit\Framework\TestCase;

class MiddlewareCollectionFactoryTest extends TestCase
{
    public function testCreateReturnNewInstanceOfMiddlewareCollection(): void
    {
        $sut = new MiddlewareCollectionFactory();
        $this->assertInstanceOf(MiddlewareCollectionInterface::class, $sut->create());
    }
}
