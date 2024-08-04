<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Symfony\EventListener;

use App\Blog\Domain\Repository\PostRepository;
use App\Blog\Domain\Route as RouteBlog;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(priority: 100)]
final readonly class SitemapListener
{
    public function __construct(
        private PostRepository $postRepository,
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
                    ], UrlGeneratorInterface::ABSOLUTE_URL),
                    $post->getPublishedAt(),
                ),
                'blog',
            );
        }

        foreach ($this->postRepository->findTags() as $tag) {
            $urls->addUrl(
                new UrlConcrete(
                    $urlGenerator->generate(RouteBlog::TAG_VIEW->value, [
                        'tag' => $tag,
                    ], UrlGeneratorInterface::ABSOLUTE_URL),
                    null,
                ),
                'blog',
            );
        }
    }
}
