<?php

namespace App\CV\Domain\Repository;

use App\CV\Domain\ProfessionalExperience;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProfessionalExperience>
 */
class ProfessionalExperienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfessionalExperience::class);
    }

    /**
     * @return ProfessionalExperience
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('professional_experience')
            ->orderBy('professional_experience.startDate', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findOneLatest(): ProfessionalExperience|null
    {
        $qb = $this->createQueryBuilder('professional_experience')
            ->orderBy('professional_experience.startDate', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}