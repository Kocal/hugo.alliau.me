<?php

declare(strict_types=1);

namespace App\Places\Infrastructure\Symfony\Controller;

use App\Places\Domain\Repository\PlaceRepository;
use App\Places\Domain\Route as RoutePlaces;
use App\Shared\Domain\HttpCache\CacheMethodsTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

final class ViewPlacesController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route('/places', name: RoutePlaces::INDEX->value, options: [
        'sitemap' => true,
    ], methods: ['GET'])]
    public function __invoke(
        Request $request,
        PlaceRepository $placeRepository,
        LoggerInterface $logger
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

        $map = new Map(
            center: new Point(0, 0),
            zoom: 2,
        );

        foreach ($placeRepository->findAll() as $place) {
            if ($place->getAddress() === null) {
                continue;
            }

            $map->addMarker(new Marker(
                position: new Point(...$place->getAddress()->getCoordinates()),
                title: $place->getAddress()->getName(),
                infoWindow: new InfoWindow(
                    content: $this->renderView('places/_info_window.html.twig', [
                        'place' => $place,
                    ]),
                ),
                extra: [
                    'icon_mask_uri' => $place->getIconMaskUri(),
                ]
            ));
        }

        return $this->render('places/home.html.twig', [
            'map' => $map,
        ], $response);
    }
}
