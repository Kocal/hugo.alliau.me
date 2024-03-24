<?php

namespace App\Http\Controller\Blog;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Routing\ValueObject\RouteName;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route("/blog", name: RouteName::BLOG_HOME, options: ['sitemap' => ['priority' => 0.7, 'changefreq' => UrlConcrete::CHANGEFREQ_MONTHLY]], methods: ['GET'], format: 'html')]
    #[Route("/blog/rss.xml", name: RouteName::BLOG_RSS, format: 'xml')]
    public function __invoke(
        PostRepository $postRepository,
        string         $_format,
    ): Response
    {
        return $this->render("blog/home.{$_format}.twig", [
            'posts' => $postRepository->findLatest(),
        ]);
    }
}
