<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\HttpCache\Adapter;

use App\Shared\Domain\HttpCache\Adapter\HttpCacheAdapter;

final class NoHttpCacheAdapter implements HttpCacheAdapter
{
    #[\Override]
    public function clearAll(): void
    {
        // no-op
    }

    #[\Override]
    public function clearUrls(string ...$urls): void
    {
        // no-op
    }
}
