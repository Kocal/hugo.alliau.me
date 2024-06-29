<?php

declare(strict_types=1);

namespace App\Shared\Domain\Mapper;

final class UnsupportedFormatException extends \InvalidArgumentException
{
    public function __construct(string $format)
    {
        parent::__construct(sprintf('Unsupported format: "%s".', $format));
    }
}
