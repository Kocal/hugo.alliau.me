<?php

namespace App\Http\Cache;

trait CacheMethodsTrait
{
    private static function computeEtag(string|CacheableEntity ...$content): string
    {
        $data = array_map(static function (string|CacheableEntity $content): string {
            return $content instanceof CacheableEntity ? $content->getEtag() : $content;
        }, $content);

        return hash('xxh3', implode('', $data));
    }
}
