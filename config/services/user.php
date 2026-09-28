<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\User\\', '../../src/User')
        ->exclude([
            '../../src/User/Domain/Data/**',
            '../../src/User/Infrastructure/Doctrine/DBAL/Type/**',
            '../../src/User/Infrastructure/Foundry/Factory/**',
        ]);

    if ($containerConfigurator->env() === 'dev' || $containerConfigurator->env() === 'test') {
        $services->load('App\\User\\Infrastructure\\Foundry\\Factory\\', '../../src/User/Infrastructure/Foundry/Factory');
    }
};
