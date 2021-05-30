<?php

namespace Pond5\AsyncRequestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('pond5_async_request');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('header')->defaultValue('X-Request-Async')->end()
                ->scalarNode('transport')->isRequired()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
