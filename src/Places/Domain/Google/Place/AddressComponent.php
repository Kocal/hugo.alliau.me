<?php

declare(strict_types=1);

namespace App\Places\Domain\Google\Place;

final readonly class AddressComponent
{
    /**
     * @param array<string> $types
     */
    public function __construct(
        public string $longName,
        public string $shortName,
        public array $types,
    ) {
    }
}
