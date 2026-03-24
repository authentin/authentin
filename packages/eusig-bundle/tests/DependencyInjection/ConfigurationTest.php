<?php

declare(strict_types=1);

namespace Authentin\EusigBundle\Tests\DependencyInjection;

use Authentin\EusigBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    #[Test]
    public function it_processes_minimal_config(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [
            [
                'dss' => [
                    'base_url' => 'http://localhost:8080/services/rest',
                ],
            ],
        ]);

        self::assertSame('http://localhost:8080/services/rest', $config['dss']['base_url']);
        self::assertSame('PAdES_BASELINE_B', $config['defaults']['signature_level']);
        self::assertSame('SHA256', $config['defaults']['digest_algorithm']);
    }

    #[Test]
    public function it_processes_full_config_with_token(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [
            [
                'dss' => [
                    'base_url' => 'http://dss.example.com/services/rest',
                ],
                'token' => [
                    'type' => 'pkcs12',
                    'path' => '/path/to/keystore.p12',
                    'password' => 'secret',
                ],
                'defaults' => [
                    'signature_level' => 'XAdES_BASELINE_LT',
                    'digest_algorithm' => 'SHA512',
                ],
            ],
        ]);

        self::assertSame('http://dss.example.com/services/rest', $config['dss']['base_url']);
        self::assertSame('pkcs12', $config['token']['type']);
        self::assertSame('/path/to/keystore.p12', $config['token']['path']);
        self::assertSame('secret', $config['token']['password']);
        self::assertSame('XAdES_BASELINE_LT', $config['defaults']['signature_level']);
        self::assertSame('SHA512', $config['defaults']['digest_algorithm']);
    }
}
