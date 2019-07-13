<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('middlewares');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('handlers')
                    ->ignoreExtraKeys(false)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
