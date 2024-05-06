<?php

namespace App\Shared\Http\Cache;

use App\Shared\Http\Cache\ValueObject\CacheItem;

interface CacheableEntity
{
    public function getEtag(): string;

    /**
     * @return array<CacheItem>
     */
    public function getCacheItems(): array;
}
