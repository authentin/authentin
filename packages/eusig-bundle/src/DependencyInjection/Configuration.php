<?php

declare(strict_types=1);

namespace Authentin\EusigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('eusig');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('dss')
                    ->isRequired()
                    ->children()
                        ->scalarNode('base_url')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->info('Base URL of the EU DSS REST API (e.g. http://localhost:8080/services/rest)')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('token')
                    ->children()
                        ->enumNode('type')
                            ->values(['pkcs12'])
                            ->defaultValue('pkcs12')
                        ->end()
                        ->scalarNode('path')
                            ->info('Path to the PKCS#12 (.p12) keystore file')
                        ->end()
                        ->scalarNode('password')
                            ->info('Password for the PKCS#12 keystore')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('defaults')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('signature_level')
                            ->defaultValue('PAdES_BASELINE_B')
                        ->end()
                        ->scalarNode('digest_algorithm')
                            ->defaultValue('SHA256')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
