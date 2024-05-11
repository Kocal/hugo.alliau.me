<?php

namespace App\Places\Infrastructure\Http\Controller;

use App\Places\Domain\Repository\PlaceRepository;
use App\Routing\Domain\ValueObject\RouteName;
use App\Shared\Http\Cache\CacheMethodsTrait;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route("/places", name: RouteName::PLACES_HOME, options: [
        'sitemap' => [
            'priority' => 0.7,
            'changefreq' => UrlConcrete::CHANGEFREQ_WEEKLY,
        ],
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
