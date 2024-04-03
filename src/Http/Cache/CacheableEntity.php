<?php

namespace App\Http\Cache;

 interface CacheableEntity
{
    public function getEtag(): string;
}