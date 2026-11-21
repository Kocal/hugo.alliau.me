<?php

declare(strict_types=1);

namespace App\Blog\Application\Image;

final readonly class ImageDimensions
{
    public function __construct(
        public int $width,
        public int $height,
    ) {
    }
}
