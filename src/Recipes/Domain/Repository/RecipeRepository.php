<?php

declare(strict_types=1);

namespace App\Recipes\Domain\Repository;

use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\RecipeSummary;

interface RecipeRepository
{
    public function add(Recipe $recipe): void;

    /**
     * @return list<RecipeSummary>
     */
    public function findAllVisible(): array;

    public function findOneBySlug(string $slug): ?Recipe;

    public function findLatestUpdated(): ?Recipe;
}
