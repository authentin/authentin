<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Authentin\Eusig\Contract\SigningClientInterface;
use Authentin\Eusig\Contract\ValidatorInterface;
use Authentin\Eusig\Dss\DssClient;
use Authentin\Eusig\Dss\DssSigningClient;
use Authentin\Eusig\Dss\DssValidator;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    // DSS HTTP client
    $services->set('eusig.dss_client', DssClient::class)
        ->args([
            service(ClientInterface::class),
            service(RequestFactoryInterface::class),
            service(StreamFactoryInterface::class),
            param('eusig.dss.base_url'),
        ]);

    // Signing client (DSS REST)
    $services->set('eusig.signing_client', DssSigningClient::class)
        ->args([
            service('eusig.dss_client'),
        ]);

    $services->alias(SigningClientInterface::class, 'eusig.signing_client');

    // Validator (DSS REST)
    $services->set('eusig.validator', DssValidator::class)
        ->args([
            service('eusig.dss_client'),
        ]);

    $services->alias(ValidatorInterface::class, 'eusig.validator')
        ->public();
};