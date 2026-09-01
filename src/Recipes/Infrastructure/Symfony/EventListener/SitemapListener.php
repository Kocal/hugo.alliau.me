<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\Symfony\EventListener;

use App\Recipes\Domain\Data\Route as RouteRecipes;
use App\Recipes\Domain\Repository\RecipeRepository;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(priority: 100)]
final readonly class SitemapListener
{
    public function __construct(
        private RecipeRepository $recipeRepository,
    ) {
    }

    public function __invoke(SitemapPopulateEvent $event): void
    {
        $urls = $event->getUrlContainer();
        $urlGenerator = $event->getUrlGenerator();

        foreach ($this->recipeRepository->findAllVisible() as $recipe) {
            $urls->addUrl(
                new UrlConcrete(
                    $urlGenerator->generate(RouteRecipes::VIEW->value, [
                        'slug' => $recipe->slug,
                        '_locale' => $recipe->locale->value,
                    ], UrlGeneratorInterface::ABSOLUTE_URL),
                    $recipe->updatedAt,
                ),
                'recipes',
            );
        }
    }
}
