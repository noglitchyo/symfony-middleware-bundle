<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\DependencyInjection;

use InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class NoGlitchYoMiddlewareExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $mainConfig = $this->getConfiguration($configs, $container);

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
                        $container->setParameter(sprintf('middlewares.handlers.%s', $handlerName), $handlerConfiguration);
                    }

                    $container->setParameter('middlewares.handlers', $config);
                    break;
            }
        }
    }

    public function getAlias()
    {
        return 'middlewares';
    }
}
