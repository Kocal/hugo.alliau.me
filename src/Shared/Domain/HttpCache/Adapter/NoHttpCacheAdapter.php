<?php

declare(strict_types=1);

namespace App\Shared\Domain\HttpCache\Adapter;

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
