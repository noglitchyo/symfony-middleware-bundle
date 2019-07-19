<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Tests\Unit\Middleware\Stack;

use InvalidArgumentException;
use NoGlitchYo\MiddlewareBundle\Entity\MiddlewareStackEntry;
use NoGlitchYo\MiddlewareBundle\Factory\MiddlewareCollectionFactory;
use NoGlitchYo\MiddlewareBundle\Middleware\Matcher\RequestMatcherInterface;
use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStack;
use NoGlitchYo\MiddlewareCollectionRequestHandler\Collection\SplStackMiddlewareCollection;
use NoGlitchYo\MiddlewareCollectionRequestHandler\MiddlewareCollectionInterface;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class MiddlewareStackTest extends TestCase
{
    /**
     * @var RequestMatcherInterface|MockObject
     */
    private $matcherMock;

    /**
     * @var MiddlewareCollectionFactory|MockObject
     */
    private $middlewareCollectionFactoryMock;

    /**
     * @var MiddlewareStack
     */
    private $sut;

    protected function setUp(): void
    {
        $this->middlewareCollectionFactoryMock = $this->createMock(MiddlewareCollectionFactory::class);
        $this->matcherMock                     = $this->createMock(RequestMatcherInterface::class);

        $this->sut = new MiddlewareStack($this->middlewareCollectionFactoryMock, $this->matcherMock);
    }

    public function testThatAddCreateNewMiddlewareStackEntryAndAddItToList(): void
    {
        $middlewareCollectionMock = $this->createMock(MiddlewareCollectionInterface::class);

        $middlewareStack = $this->sut->add($middlewareCollectionMock);

        $this->assertSame($this->sut, $middlewareStack);
    }

    public function testThatWithDefaultHandlerAssignDefaultHandlerToCurrentInstance()
    {
        $middlewareCollectionMock = $this->createMock(MiddlewareCollectionInterface::class);

        $this->sut->add($middlewareCollectionMock);

        $this->sut->withDefaultHandler(
            function () {
                $this->assertTrue(true);
            }
        );

        $requestHandler = $this->sut->compile(new Request());

        $this->assertInstanceOf(RequestHandlerInterface::class, $requestHandler);

        $requestHandler->handle(new ServerRequest('POST', '/test/uri'));
    }

    public function testThatWithDefaultHandlerThrowExceptionIfHandlerIsNotCallableOrRequestHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->sut->withDefaultHandler(new stdClass());
    }

    public function testThatWithDefaultHandlerAcceptCallable(): void
    {
        $middlewareStack = $this->sut->withDefaultHandler(
            function () {
            }
        );

        $this->assertEquals($this->sut, $middlewareStack);
    }

    public function testThatCompileCreateRequestHandlerWithCollectionsFromMatchingStackEntry()
    {
        $request = new Request();

        $middlewareCollectionMock = $this->createMock(MiddlewareCollectionInterface::class);

        $this->sut->add($middlewareCollectionMock);

        $this->middlewareCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn(new SplStackMiddlewareCollection());

        $this->matcherMock
            ->expects($this->once())
            ->method('match')
            ->with($request, $this->isInstanceOf(MiddlewareStackEntry::class))
            ->willReturn(true);

        $this->sut->compile($request);
    }
}
