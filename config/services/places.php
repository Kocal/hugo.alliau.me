<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Places\Domain\Repository\PlaceRepository;
use App\Places\Infrastructure\Doctrine\Repository\PlaceORMRepository;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\Places\\', '../../src/Places')
        ->exclude('../../src/Places/Infrastructure/Foundry/Factory/**');

    if ($containerConfigurator->env() === 'dev' || $containerConfigurator->env() === 'test') {
        $services->load('App\\Places\\Infrastructure\\Foundry\\Factory\\', '../../src/Places/Infrastructure/Foundry/Factory');
    }

    $services->set(PlaceRepository::class)
        ->class(PlaceORMRepository::class);
};
