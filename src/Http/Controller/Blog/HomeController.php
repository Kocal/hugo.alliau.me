<?php

namespace App\Http\Controller\Blog;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Routing\ValueObject\RouteName;
use App\Http\Cache\CacheMethodsTrait;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route("/blog", name: RouteName::BLOG_HOME, options: ['sitemap' => ['priority' => 0.7, 'changefreq' => UrlConcrete::CHANGEFREQ_MONTHLY]], methods: ['GET'], format: 'html')]
    #[Route("/blog/rss.xml", name: RouteName::BLOG_RSS, methods: ['GET'], format: 'xml')]
    public function __invoke(
        string         $_format,
        Request        $request,
        PostRepository $postRepository,
    ): Response
    {
        $latestPost = $postRepository->getOneLatestPublished();

        $response = new Response();
        $response->setEtag(self::computeEtag($latestPost));
        $response->setLastModified($latestPost->getPublishedAt());
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render("blog/home.{$_format}.twig", [
            'posts' => $postRepository->findLatestPublished(),
        ], $response);
    }
}
