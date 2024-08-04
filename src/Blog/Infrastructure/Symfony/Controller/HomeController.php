<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Symfony\Controller;

use App\Blog\Domain\Repository\PostRepository;
use App\Blog\Domain\Route as RouteBlog;
use App\Shared\Domain\HttpCache\CacheMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route('/blog', name: RouteBlog::HOME->value, options: [
        'sitemap' => true,
    ], methods: ['GET'], format: 'html')]
    #[Route('/blog/rss.xml', name: RouteBlog::RSS->value, methods: ['GET'], format: 'xml')]
    public function __invoke(
        string $_format,
        Request $request,
        PostRepository $postRepository,
    ): Response {
        $latestPost = $postRepository->getOneLatestPublished();

        $response = new Response();
        $response->setEtag(self::computeEtag($latestPost));
        $response->setLastModified($latestPost->getPublishedAt());
        $response->setMaxAge(60 * 60 * 24 * 14);
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render("blog/home.{$_format}.twig", [
            'posts' => $postRepository->findLatestPublished(),
        ], $response);
    }
}
