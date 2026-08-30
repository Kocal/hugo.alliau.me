<?php

declare(strict_types=1);

namespace App\Tests\Blog\Infrastructure\Symfony\Controller;

use App\Blog\Domain\Data\PostStatus;
use App\Blog\Infrastructure\Foundry\Factory\PostFactory;
use App\Shared\Domain\Data\Locale;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversNothing]
final class BlogLocaleTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    #[\Override]
    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testBlogIndexShowsLocaleFlags(): void
    {
        $client = self::createClient();

        PostFactory::createOne([
            'slug' => 'english-flag-post',
            'locale' => Locale::EN,
            'status' => PostStatus::PUBLISHED,
            'publishedAt' => new \DateTime('2020-01-01'),
        ]);
        PostFactory::createOne([
            'slug' => 'french-flag-post',
            'locale' => Locale::FR,
            'status' => PostStatus::PUBLISHED,
            'publishedAt' => new \DateTime('2020-01-02'),
        ]);

        $client->request(Request::METHOD_GET, '/blog');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('🇬🇧', (string) $client->getResponse()->getContent());
        $this->assertStringContainsString('🇫🇷', (string) $client->getResponse()->getContent());
    }

    public function testPostIsServedUnderItsOwnLocale(): void
    {
        $client = self::createClient();

        PostFactory::createOne([
            'slug' => 'english-served-post',
            'locale' => Locale::EN,
            'status' => PostStatus::PUBLISHED,
            'publishedAt' => new \DateTime('2020-01-01'),
        ]);

        $client->request(Request::METHOD_GET, '/blog/posts/english-served-post');

        $this->assertResponseIsSuccessful();
        $this->assertSame('en', $client->getRequest()->getLocale());
    }

    public function testEnglishPostUnderFrenchPrefixRedirects(): void
    {
        $client = self::createClient();

        PostFactory::createOne([
            'slug' => 'english-redirect-post',
            'locale' => Locale::EN,
            'status' => PostStatus::PUBLISHED,
            'publishedAt' => new \DateTime('2020-01-01'),
        ]);

        $client->request(Request::METHOD_GET, '/fr/blog/posts/english-redirect-post');

        $this->assertResponseRedirects('/blog/posts/english-redirect-post', 301);
    }

    public function testFrenchPostUnderEnglishPrefixRedirects(): void
    {
        $client = self::createClient();

        PostFactory::createOne([
            'slug' => 'french-redirect-post',
            'locale' => Locale::FR,
            'status' => PostStatus::PUBLISHED,
            'publishedAt' => new \DateTime('2020-01-01'),
        ]);

        $client->request(Request::METHOD_GET, '/blog/posts/french-redirect-post');

        $this->assertResponseRedirects('/fr/blog/posts/french-redirect-post', 301);
    }
}
