<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Tests\Unit\Middleware\Stack;

use NoGlitchYo\MiddlewareBundle\Entity\HandlerCondition;
use NoGlitchYo\MiddlewareBundle\Entity\MiddlewareStackEntry;
use NoGlitchYo\MiddlewareBundle\Middleware\Matcher\RequestMatcher;
use NoGlitchYo\MiddlewareCollectionRequestHandler\Collection\ArrayStackMiddlewareCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestMatcherTest extends TestCase
{
    /**
     * @var RequestMatcher
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new RequestMatcher();
    }

    public function testMatchReturnTrueIfConditionsAreNotProvided()
    {
        $request = new Request();

        $middlewareStackEntry = new MiddlewareStackEntry(
            new ArrayStackMiddlewareCollection()
        );

        $this->assertTrue($this->sut->match($request, $middlewareStackEntry));
    }

    /**
     * @dataProvider provideConditions
     */
    public function testMatchReturnTrueIfConditionAreFulfilled(
        HandlerCondition $handlerCondition,
        Request $request,
        bool $expected
    )
    {
        $middlewareStackEntry = new MiddlewareStackEntry(
            new ArrayStackMiddlewareCollection(),
            $handlerCondition
        );

        $this->assertEquals($expected, $this->sut->match($request, $middlewareStackEntry));
    }

    public function testMatchReturnFalseIfConditionsFailed()
    {

    }

    public function provideConditions()
    {
        $request = (Request::create('/route/path'));
        $request->attributes->set('_route', 'testRouteName');

        return [
            [
                (new HandlerCondition())
                    ->setRoutePath('/route/path'),
                Request::create('/route/path'),
                true,
            ],
            [
                (new HandlerCondition())
                    ->setRoutePath('/route/path'),
                Request::create('/route/path2'),
                false,
            ],
            [
                (new HandlerCondition())
                    ->setRoutePath('/route/path')
                    ->setRouteName('testRouteName'),
                $request,
                true,
            ],
            [
                (new HandlerCondition())
                    ->setRoutePath('/route/path')
                    ->setRouteName('testRouteName2'),
                $request,
                false,
            ],
        ];
    }
}