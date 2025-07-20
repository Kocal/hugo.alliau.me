<?php

declare(strict_types=1);

namespace App\Shared\Domain\HttpCache\Adapter;

interface HttpCacheAdapter
{
    public function clearAll(): void;

    public function clearUrls(string ...$urls): void;
}
