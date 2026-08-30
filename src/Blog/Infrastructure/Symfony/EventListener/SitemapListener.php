<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Symfony\EventListener;

use App\Blog\Domain\Data\Route as RouteBlog;
use App\Blog\Domain\Repository\PostRepository;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(priority: 100)]
final readonly class SitemapListener
{
    /**
     * @param list<string> $enabledLocales
     */
    public function __construct(
        private PostRepository $postRepository,
        #[Autowire(param: 'kernel.enabled_locales')]
        private array $enabledLocales,
    ) {
    }

    public function __invoke(SitemapPopulateEvent $event): void
    {
        $urls = $event->getUrlContainer();
        $urlGenerator = $event->getUrlGenerator();

        foreach ($this->postRepository->findLatestPublished() as $post) {
            $urls->addUrl(
                new UrlConcrete(
                    $urlGenerator->generate(RouteBlog::POST_VIEW->value, [
                        'slug' => $post->getSlug(),
                        '_locale' => $post->getLocale()
                            ->value,
                    ], UrlGeneratorInterface::ABSOLUTE_URL),
                    $post->getPublishedAt(),
                ),
                'blog',
            );
        }

        foreach ($this->postRepository->findTags() as $tag) {
            foreach ($this->enabledLocales as $locale) {
                $urls->addUrl(
                    new UrlConcrete(
                        $urlGenerator->generate(RouteBlog::TAG_VIEW->value, [
                            'tag' => $tag,
                            '_locale' => $locale,
                        ], UrlGeneratorInterface::ABSOLUTE_URL),
                    ),
                    'blog',
                );
            }
        }
    }
}
