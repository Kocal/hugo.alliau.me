<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Controller;

use App\Shared\Domain\Data\Route as RouteShared;
use App\Shared\Infrastructure\Symfony\HttpCache\CacheableResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    use CacheableResponseTrait;

    #[Route(path: [
        'en' => '/',
        'fr' => '/fr',
    ], name: RouteShared::HOME->value, options: [
        'sitemap' => true,
    ], methods: ['GET'])]
    public function __invoke(
        Request $request,
    ): Response {
        $response = $this->createCacheableResponse(
            etag: ['home'],
            lastModified: null,
            sharedMaxAge: 60 * 60 * 24 * 365,
        );

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render('home.html.twig', [], $response);
    }
}
