<?php

declare(strict_types=1);

namespace App\Places\Application;

final readonly class Country
{
    /**
     * @param list<array{0: float, 1: float}> $coordinates
     */
    public function __construct(
        public int $placesCount,
        public array $coordinates,
    ) {
    }
}
