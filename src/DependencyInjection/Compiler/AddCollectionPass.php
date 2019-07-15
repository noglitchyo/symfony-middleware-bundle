<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\DependencyInjection\Compiler;

use LogicException;
use NoGlitchYo\MiddlewareBundle\Entity\HandlerConfiguration\Filter;
use NoGlitchYo\MiddlewareBundle\HandlerConfiguration;
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
            $handlerFilterServiceId        = sprintf(
                'middlewares.handler_configuration_filter.%s',
                $handlerIdentifier
            );

            if (!isset($collectionReferences[$handlerConfiguration['collection']])) {
                throw new RuntimeException(
                    sprintf(
                        'Collection with service identifier `%s` is not defined.',
                        $handlerConfiguration['collection']
                    )
                );
            }


            $filterDefinition = $container->register($handlerFilterServiceId, Filter::class);

            $filter = $handlerConfiguration['filter'];

            $filterDefinition->addMethodCall('setRoutePath', [$filter['routePath'] ?? null]);
            $filterDefinition->addMethodCall('setRouteName', [$filter['routeName'] ?? null]);
            $filterDefinition->addMethodCall('setController', [$filter['controller'] ?? null]);

            $container->register($handlerConfigurationServiceId, HandlerConfiguration::class)
                ->addTag(static::HANDLER_CONFIGURATION_TAG)
                ->addMethodCall('setIdentifier', [$handlerIdentifier])
                ->addMethodCall('setCollection', [$collectionReferences[$handlerConfiguration['collection']]])
                ->addMethodCall('setFilter', [new Reference($handlerFilterServiceId)]);
        }
    }
}