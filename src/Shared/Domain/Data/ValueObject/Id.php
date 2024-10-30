<?php

declare(strict_types=1);

namespace App\Shared\Domain\Data\ValueObject;

interface Id
{
    public static function fromString(string $uuid): static;

    public function toString(): string;
}
