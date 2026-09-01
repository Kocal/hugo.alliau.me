<?php

declare(strict_types=1);

namespace App\Recipes\Domain\Data;

use Symfony\Component\Translation\TranslatableMessage;
use function Symfony\Component\Translation\t;

enum RecipeType: string
{
    case STARTER = 'starter';

    case MAIN = 'main';

    case DESSERT = 'dessert';

    public function toTranslatable(): TranslatableMessage
    {
        return t('recipe_type.' . $this->value);
    }
}
