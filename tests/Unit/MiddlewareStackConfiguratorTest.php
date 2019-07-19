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
     * @param MiddlewareCollectionInterface $collection
     * @param array|null $conditions
     */
    public function testIsInvokable(
        MiddlewareCollectionInterface $collection,
        ?array $conditions
    ): void {
        $handlers = $this->getHandlers($collection, $conditions);
        $sut      = new MiddlewareStackConfigurator($handlers);

        $this->assertTrue(is_callable($sut));
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
     * @param MiddlewareCollectionInterface $collection
     * @param array|null $conditions
     */
    public function testAddMiddlewareCollectionForEachGivenConditions(
        MiddlewareCollectionInterface $collection,
        ?array $conditions
    ) {
        $handlers = $this->getHandlers($collection, $conditions);
        $sut      = new MiddlewareStackConfigurator($handlers);

        $addCount = $conditions ? count($conditions) : 1;

        $this->middlewareStackMock
            ->expects($this->atLeast($addCount))
            ->method('add')
            ->with($collection);

        call_user_func($sut, $this->middlewareStackMock);
    }

    private function getHandlers(MiddlewareCollectionInterface $collection, ?array $conditions): array
    {
        $handler = (new HandlerConfiguration())
            ->setCollection($collection);

        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                $handler->addCondition($condition);
            }
        }

        return [$handler];
    }

    public function provideHandlers(): array
    {
        $collection = new SplStackMiddlewareCollection();
        $conditions = [new HandlerCondition()];

        return [
            [
                $collection,
                null,
            ],
            [
                $collection,
                $conditions,
            ],
        ];
    }
}
