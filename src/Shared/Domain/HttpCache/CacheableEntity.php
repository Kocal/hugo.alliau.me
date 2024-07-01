<?php

namespace App\Shared\Domain\HttpCache;

interface CacheableEntity
{
    public function getEtag(): string;

    /**
     * @return array<CacheItem>
     */
    public function getCacheItems(): array;
}
