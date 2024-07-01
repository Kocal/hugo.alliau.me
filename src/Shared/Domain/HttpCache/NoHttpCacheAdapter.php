<?php

namespace App\Shared\Domain\HttpCache;

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
