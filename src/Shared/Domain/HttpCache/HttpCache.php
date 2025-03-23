<?php

declare(strict_types=1);

namespace App\Shared\Domain\HttpCache;

use App\Shared\Domain\HttpCache\Exception\UnableToClearHttpCacheException;

interface HttpCache
{
    /**
     * Clear all cache items.
     *
     * @throws UnableToClearHttpCacheException
     */
    public function clearAll(): void;

    /**
     * Clear cache items for the given cache keys.
     *
     * @throws UnableToClearHttpCacheException
     */
    public function clearFor(CacheItem ...$cacheItems): void;
}
