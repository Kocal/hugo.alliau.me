<?php

namespace App\Http\Cache;

 use App\Http\Cache\ValueObject\CacheItem;

 interface CacheableEntity
{
    public function getEtag(): string;

     /**
      * @return array<CacheItem>
      */
     public function getCacheItems(): array;
}