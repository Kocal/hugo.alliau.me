<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\Foundry\Factory;

use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Domain\Data\RecipeType;
use App\Recipes\Domain\Data\Step;
use App\Recipes\Domain\Data\Unit;
use App\Shared\Domain\Data\Locale;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Recipe>
 */
final class RecipeFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Recipe::class;
    }

    public function visible(): static
    {
        return $this->with([
            'visible' => true,
        ]);
    }

    public function english(): static
    {
        return $this->with([
            'locale' => Locale::FR,
        ]);
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            'content' => new RecipeContent([
                new Step('s1', 'Mélanger farine de riz et eau', [
                    new Ingredient('i1', 'de farine de riz gluant', 2.0, null, Unit::TBSP),
                    new Ingredient('i2', "d'eau", 1.0, 2.0, Unit::TBSP),
                ]),
            ]),
            'locale' => Locale::EN,
            'name' => self::faker()->words(3, true),
            'servings' => self::faker()->numberBetween(1, 8),
            'slug' => self::faker()->unique()->slug(),
            'sourceLabel' => null,
            'sourceUrl' => null,
            'type' => self::faker()->randomElement(RecipeType::cases()),
            'visible' => false,
        ];
    }
}
