<?php

declare(strict_types=1);

namespace Authentin\EusigBundle\Tests\DependencyInjection;

use Authentin\Eusig\Contract\SignerInterface;
use Authentin\EusigBundle\DependencyInjection\EusigExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EusigExtensionTest extends TestCase
{
    #[Test]
    public function it_registers_signer_service(): void
    {
        $container = new ContainerBuilder();
        $extension = new EusigExtension();

        $extension->load([
            [
                'dss' => [
                    'base_url' => 'http://localhost:8080/services/rest',
                ],
            ],
        ], $container);

        self::assertTrue($container->hasDefinition('eusig.signer'));
        self::assertTrue($container->hasAlias(SignerInterface::class));
    }

    #[Test]
    public function it_sets_dss_parameters(): void
    {
        $container = new ContainerBuilder();
        $extension = new EusigExtension();

        $extension->load([
            [
                'dss' => [
                    'base_url' => 'http://dss.example.com/services/rest',
                ],
                'defaults' => [
                    'signature_level' => 'XAdES_BASELINE_LT',
                    'digest_algorithm' => 'SHA512',
                ],
            ],
        ], $container);

        self::assertSame('http://dss.example.com/services/rest', $container->getParameter('eusig.dss.base_url'));
        self::assertSame('XAdES_BASELINE_LT', $container->getParameter('eusig.defaults.signature_level'));
        self::assertSame('SHA512', $container->getParameter('eusig.defaults.digest_algorithm'));
    }

    #[Test]
    public function it_sets_token_parameters(): void
    {
        $container = new ContainerBuilder();
        $extension = new EusigExtension();

        $extension->load([
            [
                'dss' => [
                    'base_url' => 'http://localhost:8080/services/rest',
                ],
                'token' => [
                    'type' => 'pkcs12',
                    'path' => '/path/to/keystore.p12',
                    'password' => 'secret',
                ],
            ],
        ], $container);

        self::assertSame('pkcs12', $container->getParameter('eusig.token.type'));
        self::assertSame('/path/to/keystore.p12', $container->getParameter('eusig.token.path'));
        self::assertSame('secret', $container->getParameter('eusig.token.password'));
    }
}
