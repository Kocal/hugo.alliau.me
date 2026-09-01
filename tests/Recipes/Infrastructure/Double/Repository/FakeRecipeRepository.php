<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Infrastructure\Double\Repository;

use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\RecipeSummary;
use App\Recipes\Domain\Repository\RecipeRepository;

final class FakeRecipeRepository implements RecipeRepository
{
    /**
     * @var list<Recipe>
     */
    private array $recipes = [];

    #[\Override]
    public function add(Recipe $recipe): void
    {
        $this->recipes[] = $recipe;
    }

    #[\Override]
    public function findAllVisible(): array
    {
        $visible = array_values(array_filter(
            $this->recipes,
            static fn (Recipe $recipe): bool => $recipe->isVisible(),
        ));

        usort(
            $visible,
            static fn (Recipe $a, Recipe $b): int => [$a->getType()->value, $a->getName()] <=> [$b->getType()->value, $b->getName()],
        );

        return array_map(
            static fn (Recipe $recipe): RecipeSummary => new RecipeSummary(
                (string) $recipe->getName(),
                (string) $recipe->getSlug(),
                $recipe->getType(),
                $recipe->getLocale(),
                $recipe->getUpdatedAt(),
            ),
            $visible,
        );
    }

    #[\Override]
    public function findOneBySlug(string $slug): ?Recipe
    {
        foreach ($this->recipes as $recipe) {
            if ($recipe->getSlug() === $slug) {
                return $recipe;
            }
        }

        return null;
    }

    #[\Override]
    public function findLatestUpdated(): ?Recipe
    {
        if ($this->recipes === []) {
            return null;
        }

        $latest = $this->recipes[0];
        foreach ($this->recipes as $recipe) {
            if ($recipe->getUpdatedAt() > $latest->getUpdatedAt()) {
                $latest = $recipe;
            }
        }

        return $latest;
    }
}
