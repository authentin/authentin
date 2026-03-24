<?php

declare(strict_types=1);

namespace Authentin\EusigBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EusigExtension extends Extension
{
    /**
     * @param array<array-key, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /** @var array{dss: array{base_url: string}, token?: array{type?: string, path?: string, password?: string}, defaults: array{signature_level: string, digest_algorithm: string}} $config */
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container->setParameter('eusig.dss.base_url', $config['dss']['base_url']);
        $container->setParameter('eusig.defaults.signature_level', $config['defaults']['signature_level']);
        $container->setParameter('eusig.defaults.digest_algorithm', $config['defaults']['digest_algorithm']);

        if (isset($config['token'])) {
            $container->setParameter('eusig.token.type', $config['token']['type'] ?? 'pkcs12');
            $container->setParameter('eusig.token.path', $config['token']['path'] ?? '');
            $container->setParameter('eusig.token.password', $config['token']['password'] ?? '');
        }
    }
}
