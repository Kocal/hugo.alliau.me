<?php

namespace App\Shared\Domain\HttpCache;

trait CacheMethodsTrait
{
    private static function computeEtag(string|CacheableEntity|null ...$content): string
    {
        $data = array_map(static fn (string|CacheableEntity|null $content): string => match (true) {
            $content instanceof CacheableEntity => $content->getEtag(),
            $content === null => '',
            default => $content,
        }, $content);

        return hash('xxh3', implode('', $data));
    }
}
