<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\Symfony\Controller;

use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\Route as RouteRecipes;
use App\Recipes\Domain\Repository\RecipeRepository;
use App\Shared\Domain\HttpCache\CacheMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ViewController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route(path: [
        'en' => '/recipes/{slug}',
        'fr' => '/fr/recettes/{slug}',
    ], name: RouteRecipes::VIEW->value, methods: ['GET'])]
    public function __invoke(string $slug, Request $request, RecipeRepository $recipeRepository): Response
    {
        $recipe = $recipeRepository->findOneBySlug($slug);

        if (! $recipe instanceof Recipe || ! $recipe->isVisible()) {
            throw $this->createNotFoundException();
        }

        if ($request->getLocale() !== $recipe->getLocale()->value) {
            return $this->redirectToRoute(RouteRecipes::VIEW->value, [
                'slug' => $recipe->getSlug(),
                '_locale' => $recipe->getLocale()
                    ->value,
            ], Response::HTTP_MOVED_PERMANENTLY);
        }

        $response = new Response();
        $response->setEtag(self::computeEtag($recipe));
        $response->setLastModified($recipe->getUpdatedAt());
        $response->setMaxAge(60 * 60 * 24 * 30);
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render('recipes/view.html.twig', [
            'recipe' => $recipe,
        ], $response);
    }
}
