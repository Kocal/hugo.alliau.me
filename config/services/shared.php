<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Shared\Domain\HttpCache\HttpCache;
use App\Shared\Domain\HttpCache\HttpCacheAdapter;
use App\Shared\Domain\HttpCache\HttpCacheAdapterFactory;
use App\Shared\Domain\HttpCache\NoHttpCacheAdapter;
use App\Shared\Infrastructure\HttpCache\Adapter\CloudflareHttpCacheAdapter;
use App\Shared\Infrastructure\Symfony\HttpCache\SymfonyHttpCache;
use App\Shared\Infrastructure\Symfony\HttpCache\SymfonyHttpCacheAdapterFactory;
use Symfony\Component\Uid\Command\GenerateUuidCommand;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\Shared\\', '../../src/Shared')
        ->exclude([
            '../../src/Shared/Domain/HttpCache/{CacheableEntity,CacheItem}.php',
            '../../src/Shared/Domain/Data/*',
        ]);

    $services->set(HttpCache::class)
        ->class(SymfonyHttpCache::class);

    $services->set(HttpCacheAdapterFactory::class)
        ->class(SymfonyHttpCacheAdapterFactory::class);

    $services->set(HttpCacheAdapter::class)
        ->factory(service(SymfonyHttpCacheAdapterFactory::class))
        ->args([env('HTTP_CACHE_ADAPTER')]);

    $services->set(SymfonyHttpCacheAdapterFactory::class)
        ->args([
            '$adapters' => service_locator([
                'cloudflare' => service(CloudflareHttpCacheAdapter::class),
                'no' => service(NoHttpCacheAdapter::class),
            ]),
        ]);

    if ($containerConfigurator->env() === 'dev') {
        $services->set(GenerateUuidCommand::class);
    }
};
