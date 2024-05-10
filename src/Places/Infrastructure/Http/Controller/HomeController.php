<?php

namespace App\Places\Infrastructure\Http\Controller;

use App\Places\Domain\Repository\PlaceRepository;
use App\Routing\Domain\ValueObject\RouteName;
use App\Shared\Http\Cache\CacheMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route("/places", name: RouteName::PLACES_HOME, options: [], methods: ['GET'])]
    public function __invoke(
        PlaceRepository $placeRepository
    ): Response {
        return $this->render("places/home.html.twig", [
            'places' => $placeRepository->findAll(),
        ]);
    }
}