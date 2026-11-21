<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Flysystem;

use App\Blog\Application\Image\ImageDimensions;
use App\Blog\Application\Image\ImageProbe;
use League\Flysystem\FilesystemOperator;

final readonly class FlysystemImageProbe implements ImageProbe
{
    public function __construct(
        private FilesystemOperator $uploadsStorage,
    ) {
    }

    #[\Override]
    public function probe(string $key): ImageDimensions|false|null
    {
        if (! $this->uploadsStorage->fileExists($key)) {
            return null;
        }

        $size = @getimagesizefromstring($this->uploadsStorage->read($key));

        if ($size === false) {
            return false;
        }

        return new ImageDimensions($size[0], $size[1]);
    }
}
