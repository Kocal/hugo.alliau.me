<?php

declare(strict_types=1);

namespace App\Blog\Domain\Repository;

use App\Blog\Domain\Post;

interface PostRepository
{
    public function getOneLatestPublished(): Post;

    /**
     * @param list<string> $tags
     * @return list<Post>
     */
    public function findLatestPublished(array $tags = []): array;

    /**
     * @return array<string>
     */
    public function findTags(): array;

    /**
     * @return list<array{ tag: string, occurrences: int }>
     */
    public function findTagsAndOccurrences(): array;
}
