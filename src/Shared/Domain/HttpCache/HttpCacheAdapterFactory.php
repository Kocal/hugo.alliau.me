<?php

declare(strict_types=1);

namespace App\Shared\Domain\HttpCache;

interface HttpCacheAdapterFactory
{
    public function __invoke(string $name): HttpCacheAdapter;
}
