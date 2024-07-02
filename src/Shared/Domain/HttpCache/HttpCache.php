<?php

declare(strict_types=1);

namespace App\Shared\Domain\HttpCache;

interface HttpCache
{
    public function clearAll(): void;

    public function clearFor(CacheItem ...$cacheItems): void;
}
