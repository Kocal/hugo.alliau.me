<?php

namespace App\Domain\Blog\Repository;

use App\Domain\Blog\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

use function Symfony\Component\Clock\now;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return iterable<Post>
     */
    public function findLatest(): iterable
    {
        $query = $this->createQueryBuilder('post')
            ->where('post.publishedAt IS NOT NULL')
            ->andWhere('post.publishedAt <= :now')
            ->orderBy('post.publishedAt', 'DESC')
            ->setParameter('now', now(), Types::DATETIME_IMMUTABLE)
            ->getQuery();

        return $query->toIterable();
    }
}
