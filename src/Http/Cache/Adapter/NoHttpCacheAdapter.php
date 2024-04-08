<?php

namespace App\Http\Cache\Adapter;

final class NoHttpCacheAdapter implements HttpCacheAdapter
{
    #[\Override]
    public function clearUrls(string ...$urls): void
    {
        // no-op
    }
}