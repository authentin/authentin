<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Authentin\Eusig\Contract\SignerInterface;
use Authentin\Eusig\Contract\SigningClientInterface;
use Authentin\Eusig\Contract\TokenInterface;
use Authentin\Eusig\Signer;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('eusig.signer', Signer::class)
        ->args([
            service(SigningClientInterface::class),
            service(TokenInterface::class),
        ]);

    $services->alias(SignerInterface::class, 'eusig.signer')
        ->public();
};
