<?php

namespace App\Http\Cache;

interface HttpCache
{
    public function clearUrls(string ...$urls): void;
}