<?php

declare(strict_types=1);

namespace App\Recipes\Application\Grid;

final readonly class Row
{
    /**
     * @param list<Cell> $cells
     */
    public function __construct(
        public array $cells,
    ) {
    }
}
