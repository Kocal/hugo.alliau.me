<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\Doctrine\DBAL\Type;

use App\Recipes\Domain\Data\RecipeId;
use App\Shared\Infrastructure\Database\Doctrine\DBAL\Types\AbstractIdType;

final class RecipeIdType extends AbstractIdType
{
    public const string NAME = 'recipe_id';

    #[\Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    protected function getIdClass(): string
    {
        return RecipeId::class;
    }
}
