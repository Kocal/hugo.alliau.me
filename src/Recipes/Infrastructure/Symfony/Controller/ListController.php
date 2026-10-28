<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\Symfony\Controller;

use App\Recipes\Domain\Data\Route as RouteRecipes;
use App\Recipes\Domain\Repository\RecipeRepository;
use App\Shared\Infrastructure\Symfony\HttpCache\CacheableResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListController extends AbstractController
{
    use CacheableResponseTrait;

    #[Route(path: [
        'en' => '/recipes',
        'fr' => '/fr/recettes',
    ], name: RouteRecipes::LIST->value, options: [
        'sitemap' => true,
    ], methods: ['GET'])]
    public function __invoke(Request $request, RecipeRepository $recipeRepository): Response
    {
        $latest = $recipeRepository->findLatestUpdated();

        $response = $this->createCacheableResponse(
            etag: ['recipes', $latest],
            lastModified: $latest?->getUpdatedAt(),
            sharedMaxAge: 60 * 60 * 24 * 30,
        );

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render('recipes/list.html.twig', [
            'recipes' => $recipeRepository->findAllVisible(),
        ], $response);
    }
}
