<?php

declare(strict_types=1);

namespace App\Recipes\Domain\Data;

final readonly class Ingredient
{
    public function __construct(
        public string $id,
        public string $label,
        public ?float $min = null,
        public ?float $max = null,
        public ?Unit $unit = null,
    ) {
    }
}
