<?php

namespace App\Shared\Http\Cache\Adapter;

interface HttpCacheAdapter
{
    public function clearAll(): void;

    public function clearUrls(string ...$urls): void;
}
