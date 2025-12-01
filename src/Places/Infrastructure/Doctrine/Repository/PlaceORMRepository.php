<?php

declare(strict_types=1);

namespace App\Places\Infrastructure\Doctrine\Repository;

use App\Places\Domain\Data\Place;
use App\Places\Domain\Repository\PlaceRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Place>
 */
class PlaceORMRepository extends ServiceEntityRepository implements PlaceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    #[\Override]
    public function add(Place $place): void
    {
        $this->getEntityManager()->persist($place);
    }

    #[\Override]
    public function getOneLatestUpdated(): Place
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
