<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Tests\Unit;

use InvalidArgumentException;
use NoGlitchYo\MiddlewareBundle\Entity\HandlerCondition;
use NoGlitchYo\MiddlewareBundle\Entity\HandlerConfiguration;
use NoGlitchYo\MiddlewareBundle\Entity\HandlerConfigurationInterface;
use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStackInterface;
use NoGlitchYo\MiddlewareBundle\MiddlewareStackConfigurator;
use NoGlitchYo\MiddlewareCollectionRequestHandler\Collection\SplStackMiddlewareCollection;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class MiddlewareStackConfiguratorTest extends TestCase
{
    /**
     * @var MiddlewareStackInterface|MockObject
     */
    private $middlewareStackMock;

    protected function setUp(): void
    {
        $this->middlewareStackMock = $this->createMock(MiddlewareStackInterface::class);
    }

    /**
     * @dataProvider provideHandlers
     */
    public function testIsInvokable(
        MiddlewareCollectionInterface $collection,
        ?array $conditions,
        array $handlers
    ): void
    {
        $sut = new MiddlewareStackConfigurator($handlers);

        call_user_func($sut);
    }

    public function testThrowExceptionIfNotInstanceOfHandlerConfigurationInterface()
    {
        $handlersMock = [
            new stdClass(),
        ];

        $sut = new MiddlewareStackConfigurator($handlersMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Handler must be an instance of ' . HandlerConfigurationInterface::class
        );

        call_user_func($sut, $this->middlewareStackMock);
    }

    /**
     * @dataProvider provideHandlers
     */
    public function testAddMiddlewareCollectionForEachGivenConditions(
        MiddlewareCollectionInterface $collection,
        ?array $conditions,
        array $handlers
    )
    {
        $sut = new MiddlewareStackConfigurator($handlers);

        $this->middlewareStackMock
            ->expects($this->atLeast(2))
            ->method('add')
            ->with($collection);

        call_user_func($sut, $this->middlewareStackMock);
    }

    /**
     * @dataProvider provideHandlers
     */
    public function testAddMiddlewareCollectionWhenNoCondition(
        MiddlewareCollectionInterface $collection,
        ?array $conditions,
        array $handlers
    )
    {

    }

    public function provideHandlers(): array
    {
        $collection = new SplStackMiddlewareCollection();
        $conditions = [new HandlerCondition()];

        return [
            [
                $collection,
                null,
                (new HandlerConfiguration())
                    ->setCollection($collection),
            ],
            [
                $collection,
                $conditions,
                (new HandlerConfiguration())
                    ->setCollection($collection)
                    ->addCondition($conditions[0]),
            ],
        ];
    }
}