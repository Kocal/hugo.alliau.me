<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Recipes\Domain\Repository\RecipeRepository;
use App\Recipes\Infrastructure\Doctrine\Repository\RecipeORMRepository;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\Recipes\\', '../../src/Recipes')
        ->exclude([
            '../../src/Recipes/Domain/Data/**',
            '../../src/Recipes/Infrastructure/Doctrine/DBAL/Type/**',
            '../../src/Recipes/Infrastructure/Foundry/Factory/**',
        ]);

    if ($containerConfigurator->env() === 'dev' || $containerConfigurator->env() === 'test') {
        $services->load('App\\Recipes\\Infrastructure\\Foundry\\Factory\\', '../../src/Recipes/Infrastructure/Foundry/Factory');
    }

    $services->set(RecipeRepository::class)
        ->class(RecipeORMRepository::class);
};
