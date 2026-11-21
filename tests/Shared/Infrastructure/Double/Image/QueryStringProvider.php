<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Double\Image;

use Symfony\UX\Image\ImageTransformation;
use Symfony\UX\Image\Provider\ProviderInterface;

final class QueryStringProvider implements ProviderInterface
{
    #[\Override]
    public function getName(): string
    {
        return 'query-string';
    }

    #[\Override]
    public function generateUrl(ImageTransformation $transformation): string
    {
        return 'https://img.test/' . $transformation->path . '?' . http_build_query(array_filter([
            'w' => $transformation->width,
            'h' => $transformation->height,
            'f' => $transformation->format,
            'q' => $transformation->quality,
        ]));
    }

    #[\Override]
    public function getSupportedOperations(): array
    {
        return [];
    }

    #[\Override]
    public function getSupportedFormats(): array
    {
        return ['webp'];
    }

    #[\Override]
    public function supportsAutoFormat(): bool
    {
        return false;
    }
}
