<?php

namespace jlekowski\AsyncRequestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('async_request');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('header')->defaultValue('X-Request-Async')->end()
                ->scalarNode('transport')->isRequired()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
