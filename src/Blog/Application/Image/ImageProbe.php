<?php

declare(strict_types=1);

namespace App\Blog\Application\Image;

interface ImageProbe
{
    /**
     * @return ImageDimensions|false|null null when the object is missing, false when it exists but its dimensions are unreadable
     */
    public function probe(string $key): ImageDimensions|false|null;
}
