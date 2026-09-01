<?php

declare(strict_types=1);

namespace App\Recipes\Application\Grid;

enum CellType
{
    case INGREDIENT;

    case STEP;

    case GAP;
}
