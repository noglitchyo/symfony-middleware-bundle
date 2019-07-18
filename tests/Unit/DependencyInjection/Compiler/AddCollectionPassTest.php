<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Tests\Unit\DependencyInjection\Compiler;

use NoGlitchYo\MiddlewareBundle\DependencyInjection\Compiler\AddCollectionPass;
use NoGlitchYo\MiddlewareCollectionRequestHandler\Collection\SplStackMiddlewareCollection;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddCollectionPassTest extends TestCase
{
    /**
     * @var AddCollectionPass
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new AddCollectionPass();
    }

    public function testThatProcessThrowExceptionIfHandlersAreDefinedButNoCollectionsWithTagsAreProvided(): void
    {
        $handlers = [
            'default' => [
                'collection' => SplStackMiddlewareCollection::class,
            ],
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'No middleware collections found. At least one collection is required to be tag with "middlewares.collection".'
        );

        $container = new ContainerBuilder();
        $container->setParameter('middlewares.handlers', $handlers);

        $this->sut->process($container);
    }

    public function testThatProcessThrowExceptionIfTheCollectionDefinedInTheHandlerConfigurationIsNotFound(): void
    {
        $container = new ContainerBuilder();

        $container->setParameter(
            'middlewares.handlers',
            [
                'default' => [
                    'collection' => 'test_identifier',
                ],
            ]
        );

        $container->register('test_identifier_2', SplStackMiddlewareCollection::class)
            ->addTag('middlewares.collection');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Collection with service identifier `test_identifier` is not defined.');

        $this->sut->process($container);
    }

    public function testThatHandlerConfigurationUseTheCollectionServiceIdToDefineTheCollectionToUse(): void
    {
        $container = new ContainerBuilder();

        $container->setParameter(
            'middlewares.handlers',
            [
                'default' => [
                    'collection' => 'test_identifier',
                ],
            ]
        );

        $container
            ->register('test_identifier', SplStackMiddlewareCollection::class)
            ->addTag('middlewares.collection');

        $this->sut->process($container);

        $definition = $container->getDefinition('middlewares.handler_configuration.default');

        $this->assertEquals(
            [
                AddCollectionPass::HANDLER_CONFIGURATION_TAG,
            ],
            array_keys($definition->getTags())
        );

        $expectedMethodCalls = [
            ['setIdentifier', ['default']],
            ['setCollection', ['test_identifier']],
        ];

        $methodCalls = $definition->getMethodCalls();

        $this->assertEquals(
            sort($expectedMethodCalls),
            sort($methodCalls)
        );
    }

    public function testThatProcessAddConditionIfConditionIsDefinedInHandlerConfiguration()
    {
        $container = new ContainerBuilder();

        $container->setParameter(
            'middlewares.handlers',
            [
                'default' => [
                    'collection' => 'test_identifier',
                    'filter'     => [
                        'routeName'  => 'testRouteName',
                        'routePath'  => '/route/test',
                        'controller' => 'App\Controller\TestController',
                    ],
                ],
            ]
        );

        $container
            ->register('test_identifier', SplStackMiddlewareCollection::class)
            ->addTag('middlewares.collection');

        $this->sut->process($container);

        $definition = $container->getDefinition('middlewares.handler_configuration.default.filter');

        $expectedMethodCalls = [
            ['setRouteName', ['testRouteName']],
            ['setRoutePath', ['/route/test']],
            ['setController', ['App\Controller\TestController']],
        ];

        $methodCalls = $definition->getMethodCalls();

        $this->assertEquals(sort($expectedMethodCalls), sort($methodCalls));
    }
}