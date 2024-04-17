<?php

namespace App\Http\Cache\Adapter;

interface HttpCacheAdapter
{
    public function clearAll(): void;

    public function clearUrls(string ...$urls): void;
}
