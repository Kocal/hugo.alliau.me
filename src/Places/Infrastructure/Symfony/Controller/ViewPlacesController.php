<?php

namespace App\Places\Infrastructure\Symfony\Controller;

use App\Places\Domain\Repository\PlaceRepository;
use App\Places\Domain\Route as RoutePlaces;
use App\Shared\Domain\HttpCache\CacheMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ViewPlacesController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route("/places", name: RoutePlaces::INDEX->value, options: [
        'sitemap' => true,
    ], methods: ['GET'])]
    public function __invoke(
        Request $request,
        PlaceRepository $placeRepository
    ): Response {
        $latestPlace = $placeRepository->getOneLatestUpdated();

        $response = new Response();
        $response->setEtag(self::computeEtag($latestPlace));
        $response->setLastModified($latestPlace->getUpdatedAt());
        $response->setMaxAge(60 * 60 * 24 * 14);
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render("places/home.html.twig", [
            'places' => $placeRepository->findAll(),
        ], $response);
    }
}
