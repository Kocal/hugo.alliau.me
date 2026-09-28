<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\HttpCache;

use App\Shared\Domain\HttpCache\Adapter\HttpCacheAdapter;
use Psr\Container\ContainerInterface;

final readonly class SymfonyHttpCacheAdapterFactory
{
    public function __construct(
        private ContainerInterface $adapters
    ) {
    }

    public function __invoke(string $name): HttpCacheAdapter
    {
        if (! $this->adapters->has($name)) {
            throw new \InvalidArgumentException(sprintf('Adapter "%s" not found.', $name));
        }

        $adapter = $this->adapters->get($name);
        if (! $adapter instanceof HttpCacheAdapter) {
            throw new \InvalidArgumentException(sprintf('Adapter "%s" must implement "%s".', $name, HttpCacheAdapter::class));
        }

        return $adapter;
    }
}
