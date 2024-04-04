<?php

namespace App\Http\Cache\Adapter;

use App\Http\Cache\HttpCache;

final class NoHttpCacheAdapter implements HttpCache
{
    #[\Override] public function clearUrls(string ...$urls): void
    {
        // no-op
    }
}