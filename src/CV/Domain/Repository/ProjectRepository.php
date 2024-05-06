<?php

namespace App\CV\Domain\Repository;

use App\CV\Domain\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('project')
            ->orderBy('project.date', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findOneLatest(): Project|null
    {
        $qb = $this->createQueryBuilder('project')
            ->orderBy('project.date', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
