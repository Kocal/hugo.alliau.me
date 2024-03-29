<?php

namespace App\Shared\Sitemap\EventListener;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Routing\ValueObject\RouteName;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(priority: 100)]
final readonly class SitemapListener
{
    public function __construct(
        private PostRepository $postRepository,
    )
    {
    }

    public function __invoke(SitemapPopulateEvent $event): void
    {
        $urls = $event->getUrlContainer();
        $urlGenerator = $event->getUrlGenerator();

        foreach ($this->postRepository->findLatest() as $post) {
            $urls->addUrl(
                new UrlConcrete(
                    $urlGenerator->generate(RouteName::BLOG_POST_VIEW, ['slug' => $post->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                    $post->getPublishedAt(),
                    UrlConcrete::CHANGEFREQ_MONTHLY,
                    0.8,
                ),
                'blog',
            );
        }

        foreach ($this->postRepository->findTags() as $tag) {
            $urls->addUrl(
               new UrlConcrete(
                     $urlGenerator->generate(RouteName::BLOG_TAG_VIEW, ['tag' => $tag], UrlGeneratorInterface::ABSOLUTE_URL),
                     null,
                     UrlConcrete::CHANGEFREQ_WEEKLY,
                     0.5,
               ),
                'blog',
            );
        }
    }
}