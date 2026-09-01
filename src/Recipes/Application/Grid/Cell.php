<?php

declare(strict_types=1);

namespace App\Recipes\Application\Grid;

use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\Step;

final readonly class Cell
{
    public function __construct(
        public CellType $type,
        public int $col,
        public int $rowSpan,
        public int $colSpan,
        public Step|Ingredient|null $node = null,
    ) {
    }

    public function isIngredient(): bool
    {
        return $this->type === CellType::INGREDIENT;
    }

    public function isStep(): bool
    {
        return $this->type === CellType::STEP;
    }

    public function isGap(): bool
    {
        return $this->type === CellType::GAP;
    }
}
