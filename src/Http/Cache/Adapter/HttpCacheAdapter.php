<?php

namespace App\Http\Cache\Adapter;

interface HttpCacheAdapter
{
    public function clearUrls(string ...$urls): void;
}