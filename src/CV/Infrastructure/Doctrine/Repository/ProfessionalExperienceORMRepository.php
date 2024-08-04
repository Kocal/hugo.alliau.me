<?php

declare(strict_types=1);

namespace App\CV\Infrastructure\Doctrine\Repository;

use App\CV\Domain\ProfessionalExperience;
use App\CV\Domain\Repository\ProfessionalExperienceRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProfessionalExperience>
 */
class ProfessionalExperienceORMRepository extends ServiceEntityRepository implements ProfessionalExperienceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfessionalExperience::class);
    }

    public function save(ProfessionalExperience $professionalExperience): void
    {
        $this->getEntityManager()->persist($professionalExperience);
    }

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
