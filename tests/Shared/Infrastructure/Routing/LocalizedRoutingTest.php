<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Routing;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

#[CoversNothing]
final class LocalizedRoutingTest extends KernelTestCase
{
    /**
     * @return iterable<string, array{string, array<string, string>, string, string}>
     */
    public static function localizedPathProvider(): iterable
    {
        // [routeName, params, locale, expectedPath]
        yield 'home en' => ['app.home', [], 'en', '/'];
        yield 'home fr' => ['app.home', [], 'fr', '/fr'];
        yield 'cv en' => ['app.cv.index', [], 'en', '/cv'];
        yield 'cv fr' => ['app.cv.index', [], 'fr', '/fr/cv'];
        yield 'blog en' => ['app.blog.home', [], 'en', '/blog'];
        yield 'blog fr' => ['app.blog.home', [], 'fr', '/fr/blog'];
        yield 'post en' => ['app.blog.posts.view', [
            'slug' => 'foo',
        ], 'en', '/blog/posts/foo'];
        yield 'post fr' => ['app.blog.posts.view', [
            'slug' => 'foo',
        ], 'fr', '/fr/blog/posts/foo'];
        yield 'tags list en' => ['app.blog.tags.list', [], 'en', '/blog/tags'];
        yield 'tags list fr' => ['app.blog.tags.list', [], 'fr', '/fr/blog/tags'];
        yield 'tag view en' => ['app.blog.tags.view', [
            'tag' => 'php',
        ], 'en', '/blog/tags/php'];
        yield 'tag view fr' => ['app.blog.tags.view', [
            'tag' => 'php',
        ], 'fr', '/fr/blog/tags/php'];
        yield 'places en' => ['app.places.view_list', [], 'en', '/places'];
        yield 'places fr' => ['app.places.view_list', [], 'fr', '/fr/lieux'];
    }

    /**
     * @param array<string, string> $params
     */
    #[DataProvider('localizedPathProvider')]
    public function testItGeneratesLocalizedPaths(string $name, array $params, string $locale, string $expected): void
    {
        self::bootKernel();
        $router = self::getContainer()->get('router');
        $this->assertInstanceOf(RouterInterface::class, $router);

        $this->assertSame($expected, $router->generate($name, $params + [
            '_locale' => $locale,
        ]));
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function matchedLocaleProvider(): iterable
    {
        // [path, expectedLocale, expectedRouteName]
        yield 'root is en' => ['/', 'en', 'app.home'];
        yield '/fr is fr' => ['/fr', 'fr', 'app.home'];
        yield '/cv is en' => ['/cv', 'en', 'app.cv.index'];
        yield '/fr/cv is fr' => ['/fr/cv', 'fr', 'app.cv.index'];
        yield '/places is en' => ['/places', 'en', 'app.places.view_list'];
        yield '/fr/lieux is fr' => ['/fr/lieux', 'fr', 'app.places.view_list'];
        yield '/fr/blog/posts is fr' => ['/fr/blog/posts/foo', 'fr', 'app.blog.posts.view'];
    }

    #[DataProvider('matchedLocaleProvider')]
    public function testItMatchesLocaleFromPath(string $path, string $expectedLocale, string $expectedRoute): void
    {
        self::bootKernel();
        $router = self::getContainer()->get('router');
        $this->assertInstanceOf(RouterInterface::class, $router);

        $match = $router->matchRequest(Request::create($path));

        $this->assertSame($expectedLocale, $match['_locale']);
        $this->assertSame($expectedRoute, $match['_route']);
    }

    public function testRssStaysUnlocalized(): void
    {
        self::bootKernel();
        $router = self::getContainer()->get('router');
        $this->assertInstanceOf(RouterInterface::class, $router);

        $this->assertSame('/blog/rss.xml', $router->generate('app.blog.rss'));
        $this->assertSame('app.blog.rss', $router->matchRequest(Request::create('/blog/rss.xml'))['_route']);
    }
}
