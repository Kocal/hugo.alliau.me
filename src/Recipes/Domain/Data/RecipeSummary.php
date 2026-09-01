<?php

declare(strict_types=1);

namespace App\Recipes\Domain\Data;

use App\Shared\Domain\Data\Locale;

/**
 * Une recette réduite à ce qu'une liste ou un sitemap affiche.
 *
 * Charger l'entité complète ferait décoder, valider et mapper l'arbre `content`
 * de chaque recette, alors qu'aucun des deux ne le lit.
 */
final readonly class RecipeSummary
{
    public function __construct(
        public string $name,
        public string $slug,
        public RecipeType $type,
        public Locale $locale,
        public \DateTimeImmutable $updatedAt,
    ) {
    }
}
