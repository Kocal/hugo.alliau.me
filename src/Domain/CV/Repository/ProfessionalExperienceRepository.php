<?php

namespace App\Domain\CV\Repository;

use App\Domain\CV\ProfessionalExperience;
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
}
