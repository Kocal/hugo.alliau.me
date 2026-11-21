<?php

declare(strict_types=1);

namespace App\Tests\Blog\Infrastructure\Symfony\Command;

use App\Blog\Domain\Data\Post;
use App\Blog\Infrastructure\Flysystem\FlysystemImageProbe;
use App\Blog\Infrastructure\Foundry\Factory\PostFactory;
use App\Blog\Infrastructure\Symfony\Command\RewriteImageUrls;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(RewriteImageUrls::class)]
#[CoversClass(FlysystemImageProbe::class)]
final class RewriteImageUrlsTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private const string LEGACY_SHOT = '![Shot](https://cdn.kocal.fr/image/https://minio.kocal.fr/hugo.alliau.me/posts-assets/a/shot.png?width=1200)';

    public function testItRewritesPostsAndPrintsReplacements(): void
    {
        $tester = $this->createTester();
        $post = PostFactory::createOne([
            'content' => self::LEGACY_SHOT,
        ]);

        $status = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertSame('![Shot](posts-assets/a/shot.png){width=2 height=1}', $this->reload($post)->getContent());
        $this->assertStringContainsString('posts-assets/a/shot.png {width=2 height=1}', $tester->getDisplay());
    }

    public function testDryRunChangesNothing(): void
    {
        $tester = $this->createTester();
        $post = PostFactory::createOne([
            'content' => self::LEGACY_SHOT,
        ]);

        $tester->execute([
            '--dry-run' => true,
        ]);

        $this->assertStringContainsString('posts-assets/a/shot.png {width=2 height=1}', $tester->getDisplay());
        $this->assertSame(self::LEGACY_SHOT, $this->reload($post)->getContent());
    }

    public function testMissingObjectsAreListed(): void
    {
        $tester = $this->createTester();
        $content = '![Gone](https://cdn.kocal.fr/image/https://minio.kocal.fr/hugo.alliau.me/posts-assets/a/missing.png)';
        $post = PostFactory::createOne([
            'content' => $content,
        ]);

        $status = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertSame($content, $this->reload($post)->getContent());
        $this->assertStringContainsString('missing.png', $tester->getDisplay());
    }

    public function testPostWhoseRewrittenContentDoesNotRenderIsNotWritten(): void
    {
        $tester = $this->createTester();
        $content = self::LEGACY_SHOT . "\n\n![x](posts-assets/a/nodims.png)";
        $post = PostFactory::createOne([
            'title' => 'Broken post',
            'content' => $content,
        ]);

        $status = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertSame($content, $this->reload($post)->getContent());
        $this->assertStringContainsString('Posts whose rewritten content does not render', $tester->getDisplay());
        $this->assertStringContainsString('Broken post', $tester->getDisplay());
        $this->assertStringContainsString('posts-assets/a/nodims.png', $tester->getDisplay());
    }

    private function reload(Post $post): Post
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        return $entityManager->find(Post::class, $post->getId()) ?? self::fail('Post not found');
    }

    private function createTester(): CommandTester
    {
        $kernel = self::bootKernel();

        $storage = self::getContainer()->get('uploads.storage');
        $this->assertInstanceOf(FilesystemOperator::class, $storage);
        $storage->write('posts-assets/a/shot.png', (string) file_get_contents(__DIR__ . '/../../../Fixtures/2x1.png'));

        $application = new Application($kernel);

        return new CommandTester($application->find('app:blog:rewrite-image-urls'));
    }
}
