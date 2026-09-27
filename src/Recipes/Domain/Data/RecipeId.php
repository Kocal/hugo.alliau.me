<?php

declare(strict_types=1);

namespace App\Recipes\Domain\Data;

use App\Shared\Domain\Data\UuidTrait;
use App\Shared\Domain\Data\ValueObject\Id;

final class RecipeId implements Id
{
    use UuidTrait;
}
