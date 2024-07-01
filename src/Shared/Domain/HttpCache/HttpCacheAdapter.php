<?php

namespace App\Shared\Domain\HttpCache;

interface HttpCacheAdapter
{
    public function clearAll(): void;

    public function clearUrls(string ...$urls): void;
}
