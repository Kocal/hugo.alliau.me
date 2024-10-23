<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\CV\Domain\Repository\ProfessionalExperienceRepository;
use App\CV\Domain\Repository\ProjectRepository;
use App\CV\Infrastructure\Doctrine\Repository\ProfessionalExperienceORMRepository;
use App\CV\Infrastructure\Doctrine\Repository\ProjectORMRepository;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\CV\\', '../../src/CV')
        ->exclude('../../src/CV/Infrastructure/Foundry/Factory/**');

    if ($containerConfigurator->env() === 'dev' || $containerConfigurator->env() === 'test') {
        $services->load('App\\CV\\Infrastructure\\Foundry\\Factory\\', '../../src/CV/Infrastructure/Foundry/Factory');
    }

    $services->set(ProjectRepository::class)
        ->class(ProjectORMRepository::class);

    $services->set(ProfessionalExperienceRepository::class)
        ->class(ProfessionalExperienceORMRepository::class);
};
