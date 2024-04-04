<?php

namespace App\Http\Cache;

use App\Http\Cache\Adapter\CloudflareHttpCacheAdapter;
use App\Http\Cache\Adapter\NoHttpCacheAdapter;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final class HttpCacheFactory
{
    public function __construct(
        #[AutowireLocator([
            'cloudflare' => CloudflareHttpCacheAdapter::class,
            'no' => NoHttpCacheAdapter::class,
        ])]
        private ContainerInterface $adapters
    )
    {
    }

    public function __invoke(string $adapter): HttpCache
    {
        if (!$this->adapters->has($adapter)) {
            throw new \InvalidArgumentException(sprintf('Adapter "%s" not found.', $adapter));
        }

        return $this->adapters->get($adapter);
    }
}