<?php

namespace App\Http\Controller;

trait CacheMethodsTrait
{
    private static function computeEtag(string $content): string
    {
        return hash('xxh3', $content);
    }
}