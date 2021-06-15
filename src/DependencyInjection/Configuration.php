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
                ->scalarNode('header')
                    ->defaultValue('X-Request-Async')
                    ->info('Header indicating that async request was sent.')
                ->end()
                ->scalarNode('transport')
                    ->isRequired()
                    ->info('Symfony messenger transport to be used for processing (storing and consuming) async requests.')
                ->end()
                ->arrayNode('methods')
                    ->defaultValue(['DELETE', 'PATCH', 'POST', 'PUT'])
                    ->cannotBeEmpty()
                    ->beforeNormalization()
                        ->ifArray()
                            ->then(function ($method) {
                                return array_map('strtoupper', $method);
                            })
                    ->end()
                    ->prototype('scalar')->end()
                    ->info('A list of HTTP methods that, when defined "header" is sent, support async requests.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
