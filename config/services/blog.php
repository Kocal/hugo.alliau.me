<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Blog\Domain\Repository\PostRepository;
use App\Blog\Infrastructure\Doctrine\Repository\PostORMRepository;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\Blog\\', '../../src/Blog')
        ->exclude('../../src/Blog/Infrastructure/Foundry/Factory/**');

    if ($containerConfigurator->env() === 'dev' || $containerConfigurator->env() === 'test') {
        $services->load('App\\Blog\\Infrastructure\\Foundry\\Factory\\', '../../src/Blog/Infrastructure/Foundry/Factory');
    }

    $services->set(PostRepository::class)
        ->class(PostORMRepository::class);
};
