<?php

declare(strict_types=1);

namespace App\Shared\Domain\HttpCache;

interface CacheableEntity
{
    public function getEtag(): string;

    /**
     * @return array<CacheItem>
     */
    public function getCacheItems(): array;
}
