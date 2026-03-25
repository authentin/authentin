<?php

declare(strict_types=1);

namespace Authentin\EusigBundle\Tests\DependencyInjection;

use Authentin\Eusig\Contract\SignerInterface;
use Authentin\Eusig\Contract\SigningClientInterface;
use Authentin\Eusig\Contract\TokenInterface;
use Authentin\Eusig\Contract\ValidatorInterface;
use Authentin\EusigBundle\DependencyInjection\EusigExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EusigExtensionTest extends TestCase
{
    #[Test]
    public function it_registers_dss_services_without_token(): void
    {
        $container = $this->loadExtension([
            'dss' => ['base_url' => 'http://localhost:8080/services/rest'],
        ]);

        self::assertTrue($container->hasDefinition('eusig.dss_client'));
        self::assertTrue($container->hasDefinition('eusig.signing_client'));
        self::assertTrue($container->hasDefinition('eusig.validator'));

        self::assertTrue($container->hasAlias(SigningClientInterface::class));
        self::assertTrue($container->hasAlias(ValidatorInterface::class));

        // Signer should NOT be registered without a token
        self::assertFalse($container->hasDefinition('eusig.signer'));
        self::assertFalse($container->hasAlias(SignerInterface::class));
    }

    #[Test]
    public function it_registers_signer_when_token_is_configured(): void
    {
        $container = $this->loadExtension([
            'dss' => ['base_url' => 'http://localhost:8080/services/rest'],
            'token' => [
                'type' => 'pkcs12',
                'path' => '/path/to/keystore.p12',
                'password' => 'secret',
            ],
        ]);

        self::assertTrue($container->hasDefinition('eusig.token'));
        self::assertTrue($container->hasAlias(TokenInterface::class));
        self::assertTrue($container->hasDefinition('eusig.signer'));
        self::assertTrue($container->hasAlias(SignerInterface::class));
    }

    #[Test]
    public function it_sets_dss_parameters(): void
    {
        $container = $this->loadExtension([
            'dss' => ['base_url' => 'http://dss.example.com/services/rest'],
            'defaults' => [
                'signature_level' => 'XAdES_BASELINE_LT',
                'digest_algorithm' => 'SHA512',
            ],
        ]);

        self::assertSame('http://dss.example.com/services/rest', $container->getParameter('eusig.dss.base_url'));
        self::assertSame('XAdES_BASELINE_LT', $container->getParameter('eusig.defaults.signature_level'));
        self::assertSame('SHA512', $container->getParameter('eusig.defaults.digest_algorithm'));
    }

    #[Test]
    public function it_rejects_token_without_path(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

        $this->loadExtension([
            'dss' => ['base_url' => 'http://localhost:8080/services/rest'],
            'token' => [
                'type' => 'pkcs12',
                'password' => 'secret',
            ],
        ]);
    }

    #[Test]
    public function it_rejects_token_without_password(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

        $this->loadExtension([
            'dss' => ['base_url' => 'http://localhost:8080/services/rest'],
            'token' => [
                'type' => 'pkcs12',
                'path' => '/path/to/keystore.p12',
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function loadExtension(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $extension = new EusigExtension();
        $extension->load([$config], $container);

        return $container;
    }
}
