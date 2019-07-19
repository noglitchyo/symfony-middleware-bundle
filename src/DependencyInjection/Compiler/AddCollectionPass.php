<?php declare(strict_types=1);
/**
 * MIT License
 *
 * Copyright (c) 2019 Maxime Elomari
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
                'No middleware collections found. At least one collection is required '.
                'to be tag with "middlewares.collection".'
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
