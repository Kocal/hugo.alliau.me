<?php

declare(strict_types=1);

namespace App\Recipes\Application\Grid;

final readonly class Grid
{
    /**
     * @param list<Row> $rows
     */
    public function __construct(
        public array $rows,
        public int $columnCount,
    ) {
    }
}
