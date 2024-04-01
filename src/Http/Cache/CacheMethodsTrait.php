<?php

namespace App\Http\Cache;

trait CacheMethodsTrait
{
    private static function computeEtag(string|ETagable ...$content): string
    {
        $data = array_map(function (string|ETagable $content): string {
            return $content instanceof ETagable ? $content->computeETag() : $content;
        }, $content);

        return hash('xxh3', implode('', $data));
    }
}