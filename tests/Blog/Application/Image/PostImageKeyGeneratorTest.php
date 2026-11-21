<?php

declare(strict_types=1);

namespace App\Tests\Blog\Application\Image;

use App\Blog\Application\Image\PostImageKeyGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PostImageKeyGenerator::class)]
final class PostImageKeyGeneratorTest extends TestCase
{
    public function testItSlugifiesTheFilenameUnderThePostPrefix(): void
    {
        $generator = new PostImageKeyGenerator();

        $key = $generator->generate('2024-05-01-coolify', 'Ma Config Coolify', 'png', static fn (): bool => false);

        $this->assertSame('posts-assets/2024-05-01-coolify/ma-config-coolify.png', $key);
    }

    public function testCollisionsGetANumericSuffix(): void
    {
        $generator = new PostImageKeyGenerator();
        $taken = ['posts-assets/s/shot.png', 'posts-assets/s/shot-2.png'];

        $key = $generator->generate('s', 'shot', 'png', static fn (string $key): bool => \in_array($key, $taken, true));

        $this->assertSame('posts-assets/s/shot-3.png', $key);
    }

    public function testEmptyBasenameFallsBackToImage(): void
    {
        $generator = new PostImageKeyGenerator();

        $key = $generator->generate('s', '', 'webp', static fn (): bool => false);

        $this->assertSame('posts-assets/s/image.webp', $key);
    }
}
