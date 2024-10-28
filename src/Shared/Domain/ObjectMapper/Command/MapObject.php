<?php

declare(strict_types=1);

namespace App\Shared\Domain\ObjectMapper\Command;

use App\Shared\Domain\ObjectMapper\Format;

/**
 * @template Class of object
 */
final readonly class MapObject
{
    /**
     * @param class-string<Class> $className
     */
    public function __construct(
        public string $className,
        public mixed $source,
        public Format $format,
    ) {
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return self<T>
     */
    public static function fromJson(string $className, string $source): self
    {
        return new self($className, $source, Format::JSON);
    }
}
