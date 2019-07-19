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

namespace NoGlitchYo\MiddlewareBundle\DependencyInjection;

use InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class NoGlitchYoMiddlewareExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $mainConfig = $this->getConfiguration($configs, $container);

        if ($mainConfig === null) {
            return;
        }

        $configuration = $this->processConfiguration($mainConfig, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        foreach ($configuration as $configName => $config) {
            switch ($configName) {
                case 'handlers':
                    foreach ($config as $handlerName => $handlerConfiguration) {
                        if (!isset($handlerConfiguration['collection'])) {
                            throw new InvalidArgumentException('Please provide a collection for handler');
                        }
                        $container->setParameter(
                            sprintf('middlewares.handlers.%s', $handlerName),
                            $handlerConfiguration
                        );
                    }

                    $container->setParameter('middlewares.handlers', $config);
                    break;
            }
        }
    }

    public function getAlias(): string
    {
        return 'middlewares';
    }
}
