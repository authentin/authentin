<?php

declare(strict_types=1);

namespace Authentin\EusigBundle\DependencyInjection;

use Authentin\Eusig\Model\DigestAlgorithm;
use Authentin\Eusig\Model\SignatureLevel;
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
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->info('Path to the PKCS#12 (.p12) keystore file')
                        ->end()
                        ->scalarNode('password')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->info('Password for the PKCS#12 keystore (use %%env()%% to avoid plain text)')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('defaults')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('signature_level')
                            ->values(\array_map(static fn(SignatureLevel $l): string => $l->value, SignatureLevel::cases()))
                            ->defaultValue('PAdES_BASELINE_B')
                        ->end()
                        ->enumNode('digest_algorithm')
                            ->values(\array_map(static fn(DigestAlgorithm $a): string => $a->value, DigestAlgorithm::cases()))
                            ->defaultValue('SHA256')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
