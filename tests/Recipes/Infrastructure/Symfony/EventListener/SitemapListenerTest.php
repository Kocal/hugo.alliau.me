<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Infrastructure\Symfony\EventListener;

use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Infrastructure\Symfony\EventListener\SitemapListener;
use App\Shared\Domain\Data\Locale;
use App\Tests\Recipes\Infrastructure\Double\Repository\FakeRecipeRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(SitemapListener::class)]
#[UsesClass(Recipe::class)]
final class SitemapListenerTest extends TestCase
{
    public function testItAddsOneUrlPerVisibleRecipeUnderItsOwnLocale(): void
    {
        $repository = new FakeRecipeRepository();
        $repository->add(new Recipe()->setSlug('udon')->setVisible(true)->setLocale(Locale::EN));
        $repository->add(new Recipe()->setSlug('tarte')->setVisible(true)->setLocale(Locale::FR));
        $repository->add(new Recipe()->setSlug('draft')->setLocale(Locale::EN));

        $added = [];
        $container = $this->createStub(UrlContainerInterface::class);
        $container->method('addUrl')
            ->willReturnCallback(static function (UrlConcrete $url, string $section) use (&$added): void {
                $added[$section][] = $url->getLoc();
            });

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturnCallback(static fn (string $route, array $params): string => \sprintf(
                'https://example.test/%s/%s',
                $params['_locale'],
                $params['slug'],
            ));

        $event = new SitemapPopulateEvent($container, $urlGenerator);

        (new SitemapListener($repository))($event);

        $this->assertSame([
            'recipes' => [
                'https://example.test/en/udon',
                'https://example.test/fr/tarte',
            ],
        ], $added);
    }
}
