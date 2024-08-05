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

    $services->load('App\\Places\\', '../../src/Places');

    $services->set(PlaceRepository::class)
        ->class(PlaceORMRepository::class);
};
