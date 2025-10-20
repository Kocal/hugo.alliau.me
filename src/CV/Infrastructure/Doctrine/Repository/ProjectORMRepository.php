<?php

declare(strict_types=1);

namespace App\CV\Infrastructure\Doctrine\Repository;

use App\CV\Domain\Project;
use App\CV\Domain\Repository\ProjectRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectORMRepository extends ServiceEntityRepository implements ProjectRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    #[\Override]
    public function save(Project $project): void
    {
        $this->getEntityManager()->persist($project);
    }

    #[\Override]
    public function findAllVisible(): array
    {
        $qb = $this->createQueryBuilder('project')
            ->where('project.visible = :isVisible')
            ->setParameter('isVisible', true)
            ->orderBy('project.date', 'DESC');

        return $qb->getQuery()->getResult();
    }

    #[\Override]
    public function findOneLatest(): Project|null
    {
        $qb = $this->createQueryBuilder('project')
            ->orderBy('project.date', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
