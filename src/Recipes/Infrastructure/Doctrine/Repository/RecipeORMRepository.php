<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\Doctrine\Repository;

use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\RecipeSummary;
use App\Recipes\Domain\Repository\RecipeRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeORMRepository extends ServiceEntityRepository implements RecipeRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    #[\Override]
    public function add(Recipe $recipe): void
    {
        $this->getEntityManager()
            ->persist($recipe);
    }

    #[\Override]
    public function findAllVisible(): array
    {
        return $this->createQueryBuilder('r')
            ->select(\sprintf('NEW %s(r.name, r.slug, r.type, r.locale, r.updatedAt)', RecipeSummary::class))
            ->andWhere('r.visible = true')
            ->orderBy('r.type', 'ASC')
            ->addOrderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function findOneBySlug(string $slug): ?Recipe
    {
        return $this->findOneBy([
            'slug' => $slug,
        ]);
    }

    #[\Override]
    public function findLatestUpdated(): ?Recipe
    {
        $result = $this->createQueryBuilder('r')
            ->orderBy('r.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof Recipe ? $result : null;
    }
}
