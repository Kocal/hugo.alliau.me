<?php

namespace App\Domain\Blog\Repository;

use App\Domain\Blog\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\ResultSetMapping;
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
    public function findLatest(array $tags = []): iterable
    {
        $qb = $this->createQueryBuilder('post')
            ->where('post.publishedAt IS NOT NULL')
            ->andWhere('post.publishedAt <= :now')
            ->orderBy('post.publishedAt', 'DESC')
            ->setParameter('now', now(), Types::DATETIME_IMMUTABLE);

        if ($tags !== []) {
            $qb->andWhere('CONTAINS(post.tags, :tags) = true')
                ->setParameter('tags', $tags, Types::JSON);
        }

        $query = $qb->getQuery();

        return $query->toIterable();
    }

    /**
     * @return list<array{ tag: string, occurences: int }>
     */
    public function findAllTags(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('tag', 'tag');
        $rsm->addScalarResult('occurences', 'occurences');

        $nativeQuery = $this->getEntityManager()->createNativeQuery(<<<SQL
            SELECT tag, COUNT(tag) AS occurences
            FROM (
                SELECT JSONB_ARRAY_ELEMENTS_TEXT(tags) AS tag
                FROM {$this->getEntityManager()->getClassMetadata(Post::class)->getTableName()}
            ) 
            GROUP BY tag
            ORDER BY occurences DESC
SQL, $rsm);

        return $nativeQuery->execute();
    }
}
