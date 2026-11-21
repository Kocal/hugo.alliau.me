<?php

declare(strict_types=1);

namespace App\Tests\Blog\Application\Double;

use App\Blog\Application\Image\ImageDimensions;
use App\Blog\Application\Image\ImageProbe;

final class InMemoryImageProbe implements ImageProbe
{
    /**
     * @var list<string>
     */
    public array $probed = [];

    /**
     * @param array<string, ImageDimensions|false> $objects
     */
    public function __construct(
        private readonly array $objects = [],
    ) {
    }

    #[\Override]
    public function probe(string $key): ImageDimensions|false|null
    {
        $this->probed[] = $key;

        return $this->objects[$key] ?? null;
    }
}
