<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Doctrine\Migration\SafeSqlGenerator;
use App\Doctrine\Migration\SqlParser;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // SqlParser service
    $services->set(SqlParser::class);

    // SafeSqlGeneratorFactory as a service that creates SafeSqlGenerator instances
    $services->set('App\Doctrine\Migration\SafeSqlGeneratorFactory')
        ->arg('$parser', service(SqlParser::class))
        ->arg('$configuration', service('doctrine.migrations.configuration'))
        ->arg('$connection', service('doctrine.dbal.default_connection'));
};
