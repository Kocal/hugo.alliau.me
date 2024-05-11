<?php

namespace App\Places\Domain\Repository;

use App\Places\Domain\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Place>
 */
class PlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    public function getOneLatestUpdated(): Place
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
