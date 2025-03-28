<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\User\Domain\Repository\UserRepository;
use App\User\Infrastructure\Doctrine\Repository\UserORMRepository;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\User\\', '../../src/User')
        ->exclude('../../src/User/Infrastructure/Foundry/Factory/**');

    if ($containerConfigurator->env() === 'dev' || $containerConfigurator->env() === 'test') {
        $services->load('App\\User\\Infrastructure\\Foundry\\Factory\\', '../../src/User/Infrastructure/Foundry/Factory');
    }

    $services->set(UserRepository::class)
        ->class(UserORMRepository::class);
};
