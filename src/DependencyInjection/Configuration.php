<?php

declare(strict_types=1);

namespace PTS\SyliusReferralPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('pts_sylius_referral_plugin');
        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('pts_sylius_referral_plugin');
        }

        $rootNode
            ->children()
                ->arrayNode('customers')
                ->children()
                    ->arrayNode('enroller_edit')
                        ->children()
                        ->booleanNode('enabled')->defaultFalse()
                        ->end()
                ->end()
            ->end();

        $rootNode
            ->children()
            ->arrayNode('channel_paths')
            ->arrayPrototype()
            ->children()
            ->scalarNode('name')->end()
            ->scalarNode('path')
            ->defaultValue('/')
            ->end()
            ->scalarNode('domain')
            ->defaultValue('*')
            ->end()
            ->booleanNode('default')
            ->defaultValue(false)
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
