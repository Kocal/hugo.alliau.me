<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Shared\Domain\HttpCache\HttpCache;
use App\Shared\Domain\HttpCache\HttpCacheAdapter;
use App\Shared\Domain\HttpCache\HttpCacheAdapterFactory;
use App\Shared\Domain\HttpCache\NoHttpCacheAdapter;
use App\Shared\Infrastructure\Cloudflare\HttpCache\Adapter\CloudflareHttpCacheAdapter;
use App\Shared\Infrastructure\Symfony\HttpCache\SymfonyHttpCache;
use App\Shared\Infrastructure\Symfony\HttpCache\SymfonyHttpCacheAdapterFactory;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\Shared\\', '../../src/Shared')
        ->exclude([
            '../../src/Shared/Domain/HttpCache/{CacheableEntity,CacheItem}.php',
            '../../src/Shared/Markdown/Extension/GitHubEmojis/data/*.php',
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
            'adapters' => service_locator([
                'cloudflare' => service(CloudflareHttpCacheAdapter::class),
                'no' => service(NoHttpCacheAdapter::class),
            ]),
        ]);
};
