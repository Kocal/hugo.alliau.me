<?php

namespace App\Http\Cache;

interface ETagable
{
    public function computeETag(): string;
}