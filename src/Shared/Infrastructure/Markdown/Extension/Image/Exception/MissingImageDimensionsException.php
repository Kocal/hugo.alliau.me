<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Markdown\Extension\Image\Exception;

final class MissingImageDimensionsException extends \InvalidArgumentException
{
    public static function forUrl(string $url): self
    {
        return new self(\sprintf('The image "%s" needs "width" and "height" attributes, e.g. ![alt](%1$s){width=1200 height=630}.', $url));
    }
}
