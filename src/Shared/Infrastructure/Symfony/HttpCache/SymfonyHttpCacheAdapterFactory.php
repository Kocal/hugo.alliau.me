<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\HttpCache;

use App\Shared\Domain\HttpCache\Adapter\HttpCacheAdapter;
use App\Shared\Domain\HttpCache\Adapter\HttpCacheAdapterFactory;
use Psr\Container\ContainerInterface;

final readonly class SymfonyHttpCacheAdapterFactory implements HttpCacheAdapterFactory
{
    public function __construct(
        private ContainerInterface $adapters
    ) {
    }

    #[\Override]
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
