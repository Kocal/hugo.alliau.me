<?php

declare(strict_types=1);

namespace App\Shared\Domain\Mapper\Command;

use App\Shared\Domain\Mapper\Format;

/**
 * @template Class of object
 */
final readonly class MapObject
{
    /**
     * @param class-string<Class> $className
     * @return self<Class>
     */
    public static function fromJson(string $className, string $source): self
    {
        return new self($className, $source, Format::JSON);
    }

    /**
     * @param class-string<Class> $className
     */
    public function __construct(
        public string $className,
        public mixed $source,
        public Format $format,
    ) {
    }
}
