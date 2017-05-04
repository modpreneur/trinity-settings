<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Trinity\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('trinity_settings');

        $rootNode
            ->children()
                ->arrayNode('settings')
                    ->useAttributeAsKey('') //the string value is not important
                    ->prototype('scalar')
                    ->isRequired()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
