<?php

declare(strict_types=1);

namespace Authentin\EusigBundle\DependencyInjection;

use Authentin\Eusig\Contract\SignerInterface;
use Authentin\Eusig\Contract\SigningClientInterface;
use Authentin\Eusig\Contract\TokenInterface;
use Authentin\Eusig\Signer;
use Authentin\Eusig\Token\Pkcs12Token;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class EusigExtension extends Extension
{
    /**
     * @param array<array-key, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /** @var array{dss: array{base_url: string}, token?: array{type?: string, path: string, password: string}, defaults: array{signature_level: string, digest_algorithm: string}} $config */
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container->setParameter('eusig.dss.base_url', $config['dss']['base_url']);
        $container->setParameter('eusig.defaults.signature_level', $config['defaults']['signature_level']);
        $container->setParameter('eusig.defaults.digest_algorithm', $config['defaults']['digest_algorithm']);

        if (isset($config['token'])) {
            $this->registerToken($config['token'], $container);
            $this->registerSigner($container);
        }
    }

    /**
     * @param array{type?: string, path: string, password: string} $tokenConfig
     */
    private function registerToken(array $tokenConfig, ContainerBuilder $container): void
    {
        $type = $tokenConfig['type'] ?? 'pkcs12';

        if ('pkcs12' === $type) {
            $definition = new Definition(Pkcs12Token::class);
            $definition->setFactory([Pkcs12Token::class, 'fromFile']);
            $definition->setArguments([
                $tokenConfig['path'],
                $tokenConfig['password'],
            ]);

            $container->setDefinition('eusig.token', $definition);
            $container->setAlias(TokenInterface::class, 'eusig.token');
        }
    }

    private function registerSigner(ContainerBuilder $container): void
    {
        $definition = new Definition(Signer::class);
        $definition->setArguments([
            new Reference(SigningClientInterface::class),
            new Reference(TokenInterface::class),
        ]);

        $container->setDefinition('eusig.signer', $definition);
        $container->setAlias(SignerInterface::class, 'eusig.signer')->setPublic(true);
    }
}
