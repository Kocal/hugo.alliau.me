<?php

declare(strict_types=1);

namespace App\Tests\Blog\Domain\Data;

use App\Blog\Domain\Data\Post;
use App\Blog\Domain\Data\PostSeo;
use App\Blog\Domain\Data\PostStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Post::class)]
#[UsesClass(PostSeo::class)]
final class PostTest extends TestCase
{
    /**
     * @return iterable<string, array{PostStatus, ?\DateTimeImmutable, bool}>
     */
    public static function provideVisibility(): iterable
    {
        yield 'draft published in the past' => [PostStatus::DRAFT, new \DateTimeImmutable('2026-01-01 10:00:00'), false];
        yield 'published in the past' => [PostStatus::PUBLISHED, new \DateTimeImmutable('2026-01-01 10:00:00'), true];
        yield 'published right now' => [PostStatus::PUBLISHED, new \DateTimeImmutable('2026-06-15 12:00:00'), true];
        yield 'published in the future' => [PostStatus::PUBLISHED, new \DateTimeImmutable('2026-12-31 10:00:00'), false];
        yield 'published without date' => [PostStatus::PUBLISHED, null, false];
    }

    #[DataProvider('provideVisibility')]
    public function testIsPubliclyVisible(PostStatus $status, ?\DateTimeImmutable $publishedAt, bool $expected): void
    {
        $post = new Post()
            ->setStatus($status)
            ->setPublishedAt($publishedAt);

        $this->assertSame($expected, $post->isPubliclyVisible(new \DateTimeImmutable('2026-06-15 12:00:00')));
    }
}
