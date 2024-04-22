<?php

namespace App\Domain\Blog\Repository;

use App\Domain\Blog\Post;
use App\Domain\Blog\PostStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
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

    public function getOneLatestPublished(): Post
    {
        return $this->getLatestPublishedQueryBuilder()
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param list<string> $tags
     * @return list<Post>
     */
    public function findLatestPublished(array $tags = []): array
    {
        $qb = $this->getLatestPublishedQueryBuilder();

        if ($tags !== []) {
            $qb->andWhere('CONTAINS(post.tags, :tags) = true')
                ->setParameter('tags', $tags, Types::JSON);
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * @return array<string>
     */
    public function findTags(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('tag', 'tag');

        $nativeQuery = $this->getEntityManager()->createNativeQuery(<<<SQL
            SELECT DISTINCT JSONB_ARRAY_ELEMENTS_TEXT(tags) AS tag
            FROM {$this->getEntityManager()->getClassMetadata(Post::class)->getTableName()}
SQL, $rsm);

        return $nativeQuery->getSingleColumnResult();
    }

    /**
     * @return list<array{ tag: string, occurrences: int }>
     */
    public function findTagsAndOccurrences(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('tag', 'tag');
        $rsm->addScalarResult('occurrences', 'occurrences');

        $nativeQuery = $this->getEntityManager()->createNativeQuery(<<<SQL
            SELECT tag, COUNT(tag) AS occurrences
            FROM (
                SELECT JSONB_ARRAY_ELEMENTS_TEXT(tags) AS tag
                FROM {$this->getEntityManager()->getClassMetadata(Post::class)->getTableName()}
            ) 
            GROUP BY tag
            ORDER BY occurrences DESC
SQL, $rsm);

        return $nativeQuery->execute();
    }

    private function getLatestPublishedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('post')
            ->where('post.status = :status')
            ->andWhere('post.publishedAt IS NOT NULL')
            ->andWhere('post.publishedAt <= :now')
            ->orderBy('post.publishedAt', 'DESC')
            ->setParameter('status', PostStatus::PUBLISHED)
            ->setParameter('now', now(), Types::DATETIME_IMMUTABLE);
    }
}
