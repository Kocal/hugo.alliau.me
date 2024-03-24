<?php

namespace App\Http\Controller;

use App\Domain\Routing\ValueObject\RouteName;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route("/", name: RouteName::HOME, options: ['sitemap' => ['priority' => 0.5, 'changefreq' => UrlConcrete::CHANGEFREQ_YEARLY]])]
    public function __invoke(): Response
    {
        return $this->render("home.html.twig");
    }
}
