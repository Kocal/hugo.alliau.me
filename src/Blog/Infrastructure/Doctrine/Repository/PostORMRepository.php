<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Doctrine\Repository;

use App\Blog\Domain\Post;
use App\Blog\Domain\PostStatus;
use App\Blog\Domain\Repository\PostRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\Clock\now;

/**
 * @extends ServiceEntityRepository<Post>
 */
final class PostORMRepository extends ServiceEntityRepository implements PostRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    #[\Override]
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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
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
