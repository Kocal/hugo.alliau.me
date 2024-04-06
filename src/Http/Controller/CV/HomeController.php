<?php

namespace App\Http\Controller\CV;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Routing\ValueObject\RouteName;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route("/cv", name: RouteName::CV_HOME, options: [
        'sitemap' => [
            'priority' => 0.7,
            'changefreq' => UrlConcrete::CHANGEFREQ_MONTHLY,
        ],
    ], methods: ['GET'], format: 'html')]
    public function __invoke(
        PostRepository $postRepository,
        string $_format,
    ): Response {
        return $this->render("cv/home.html.twig");
    }
}
