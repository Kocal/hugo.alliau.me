<?php

declare(strict_types=1);

namespace App\Tests\Blog\Infrastructure\Symfony\Controller\Post;

use App\Blog\Domain\Data\PostStatus;
use App\Blog\Infrastructure\Foundry\Factory\PostFactory;
use App\Blog\Infrastructure\Symfony\Controller\Post\ViewController;
use App\User\Infrastructure\Foundry\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(ViewController::class)]
final class ViewControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    #[\Override]
    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testPublishedPostIsPubliclyCached(): void
    {
        $client = self::createClient();

        PostFactory::createOne([
            'slug' => 'published-post',
            'status' => PostStatus::PUBLISHED,
            'publishedAt' => new \DateTimeImmutable('-1 day'),
        ]);

        $client->request(Request::METHOD_GET, '/blog/posts/published-post');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Cache-Control', 'max-age=2592000, public');
    }

    public function testScheduledPostIsNotFound(): void
    {
        $client = self::createClient();

        PostFactory::createOne([
            'slug' => 'scheduled-post',
            'status' => PostStatus::PUBLISHED,
            'publishedAt' => new \DateTimeImmutable('+1 day'),
        ]);

        $client->request(Request::METHOD_GET, '/blog/posts/scheduled-post');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDraftPreviewIsNotFoundForAnonymousVisitors(): void
    {
        $client = self::createClient();

        PostFactory::createOne([
            'slug' => 'draft-post',
            'status' => PostStatus::DRAFT,
        ]);

        $client->request(Request::METHOD_GET, '/blog/posts/draft-post?preview=true');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDraftPreviewIsServedPrivatelyToAdmins(): void
    {
        $client = self::createClient();

        PostFactory::createOne([
            'slug' => 'draft-post',
            'status' => PostStatus::DRAFT,
        ]);

        $client->loginUser(UserFactory::createOne([
            'roles' => ['ROLE_ADMIN'],
        ]));

        $client->request(Request::METHOD_GET, '/blog/posts/draft-post?preview=true');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('X-Robots-Tag', 'noindex, nofollow');
        $this->assertStringContainsString('private', (string) $client->getResponse()->headers->get('Cache-Control'));
    }
}
