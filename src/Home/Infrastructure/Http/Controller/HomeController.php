<?php

namespace App\Home\Infrastructure\Http\Controller;

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

    #[Route("/", name: RouteName::HOME, options: [
        'sitemap' => [
            'priority' => 0.5,
            'changefreq' => UrlConcrete::CHANGEFREQ_YEARLY,
        ],
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

        return $this->render("home.html.twig", [], $response);
    }
}
