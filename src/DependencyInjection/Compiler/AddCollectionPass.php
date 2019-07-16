<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\DependencyInjection\Compiler;

use LogicException;
use NoGlitchYo\MiddlewareBundle\Entity\HandlerCondition;
use NoGlitchYo\MiddlewareBundle\Entity\HandlerConfiguration;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddCollectionPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public const COLLECTION_TAG = 'middlewares.collection';
    public const HANDLER_CONFIGURATION_TAG = 'middlewares.handler_configuration';

    public function process(ContainerBuilder $container): void
    {
        $collections = $this->findAndSortTaggedServices(static::COLLECTION_TAG, $container);
        if (!$collections) {
            throw new LogicException(
                'No middleware collections found. You need to tag at least one with "middlewares.collection".'
            );
        }

        $collectionReferences = [];
        foreach ($collections as $collection) {
            $serviceId                        = (string)$collection;
            $collectionReferences[$serviceId] = $collection;
        }

        $handlersConfiguration = $container->getParameter('middlewares.handlers');

        foreach ($handlersConfiguration as $handlerIdentifier => $handlerConfiguration) {
            $handlerConfigurationServiceId = sprintf('middlewares.handler_configuration.%s', $handlerIdentifier);

            if (!isset($collectionReferences[$handlerConfiguration['collection']])) {
                throw new RuntimeException(
                    sprintf(
                        'Collection with service identifier `%s` is not defined.',
                        $handlerConfiguration['collection']
                    )
                );
            }

            $handlerDefinition = $container->register($handlerConfigurationServiceId, HandlerConfiguration::class)
                ->addTag(static::HANDLER_CONFIGURATION_TAG)
                ->addMethodCall('setIdentifier', [$handlerIdentifier])
                ->addMethodCall('setCollection', [$collectionReferences[$handlerConfiguration['collection']]]);

            $condition = $handlerConfiguration['filter'] ?? null;

            if ($condition) {
                $handlerFilterServiceId = sprintf('middlewares.handler_configuration_filter.%s', $handlerIdentifier);
                $container->register($handlerFilterServiceId, HandlerCondition::class)
                    ->addMethodCall('setRoutePath', [$condition['routePath'] ?? null])
                    ->addMethodCall('setRouteName', [$condition['routeName'] ?? null])
                    ->addMethodCall('setController', [$condition['controller'] ?? null]);

                $handlerDefinition->addMethodCall('addCondition', [new Reference($handlerFilterServiceId)]);
            }
        }
    }
}