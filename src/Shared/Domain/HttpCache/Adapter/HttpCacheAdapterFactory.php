<?php

declare(strict_types=1);

namespace App\Shared\Domain\HttpCache\Adapter;

interface HttpCacheAdapterFactory
{
    public function __invoke(string $name): HttpCacheAdapter;
}
