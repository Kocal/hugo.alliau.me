<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Controller;

use App\Shared\Domain\Data\Route as RouteShared;
use App\Shared\Domain\HttpCache\CacheMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route('/', name: RouteShared::HOME->value, options: [
        'sitemap' => true,
    ], methods: ['GET'])]
    public function __invoke(
        Request $request,
    ): Response {
        $response = new Response();
        $response->setEtag(self::computeEtag('home'));
        $response->setMaxAge(60 * 60 * 24 * 365);
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render('home.html.twig', [], $response);
    }
}
