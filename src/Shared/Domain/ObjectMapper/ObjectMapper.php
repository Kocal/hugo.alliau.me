<?php

declare(strict_types=1);

namespace App\Shared\Domain\ObjectMapper;

interface ObjectMapper
{
    /**
     * @template Class of object
     * @param class-string<Class> $className
     * @return Class
     */
    public function map(string $className, mixed $source, Format $format): mixed;
}
