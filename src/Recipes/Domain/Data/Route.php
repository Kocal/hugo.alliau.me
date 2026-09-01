<?php

declare(strict_types=1);

namespace App\Recipes\Domain\Data;

enum Route: string
{
    case LIST = 'app.recipes.list';

    case VIEW = 'app.recipes.view';
}
