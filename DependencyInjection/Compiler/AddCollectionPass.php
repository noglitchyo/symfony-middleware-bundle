<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\DependencyInjection\Compiler;

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
        if ($container->getParameter('middlewares.handlers')) {
            $collections = $this->getTaggedCollections($container);

            foreach ($container->getParameter('middlewares.handlers') as $handlerIdentifier => $handlerConfiguration) {
                if (!isset($collections[$this->getHandlerCollectionName($handlerConfiguration)])) {
                    throw new RuntimeException(
                        sprintf(
                            'Collection with service identifier `%s` is not defined.',
                            $this->getHandlerCollectionName($handlerConfiguration)
                        )
                    );
                }

                $handlerDefinition = $container
                    ->register($this->getHandlerServiceId($handlerIdentifier), HandlerConfiguration::class)
                    ->addTag(static::HANDLER_CONFIGURATION_TAG)
                    ->addMethodCall(
                        'setCollection',
                        [$collections[$this->getHandlerCollectionName($handlerConfiguration)]]
                    );

                $condition = $this->getHandlerCondition($handlerConfiguration);

                if ($condition) {
                    $container
                        ->register($this->getHandlerConditionServiceId($handlerIdentifier), HandlerCondition::class)
                        ->addMethodCall('setRoutePath', [$condition['routePath'] ?? null])
                        ->addMethodCall('setRouteName', [$condition['routeName'] ?? null])
                        ->addMethodCall('setController', [$condition['controller'] ?? null]);

                    $handlerDefinition->addMethodCall(
                        'addCondition',
                        [new Reference($this->getHandlerConditionServiceId($handlerIdentifier))]
                    );
                }
            }
        }
    }

    private function getHandlerConditionServiceId(string $handlerIdentifier): string
    {
        return sprintf('middlewares.handler_configuration.%s.condition', $handlerIdentifier);
    }

    private function getHandlerServiceId(string $handlerIdentifier): string
    {
        return sprintf('middlewares.handler_configuration.%s', $handlerIdentifier);
    }

    private function getHandlerCollectionName(array $handlerConfiguration): string
    {
        return $handlerConfiguration['collection'];
    }

    private function getHandlerCondition(array $handlerConfiguration): ?array
    {
        return $handlerConfiguration['condition'] ?? null;
    }

    private function getTaggedCollections(ContainerBuilder $container): array
    {
        $collections = $this->findAndSortTaggedServices(static::COLLECTION_TAG, $container);
        if (!$collections) {
            throw new RuntimeException(
                'No middleware collections found. At least one collection is required to be tag with "middlewares.collection".'
            );
        }

        $collectionReferences = [];
        foreach ($collections as $collection) {
            $serviceId                        = (string)$collection;
            $collectionReferences[$serviceId] = $collection;
        }

        return $collectionReferences;
    }
}
