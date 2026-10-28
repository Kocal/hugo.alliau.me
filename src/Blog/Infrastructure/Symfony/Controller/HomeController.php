<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Symfony\Controller;

use App\Blog\Domain\Data\Route as RouteBlog;
use App\Blog\Domain\Repository\PostRepository;
use App\Shared\Infrastructure\Symfony\HttpCache\CacheableResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    use CacheableResponseTrait;

    #[Route(path: [
        'en' => '/blog',
        'fr' => '/fr/blog',
    ], name: RouteBlog::HOME->value, options: [
        'sitemap' => true,
    ], methods: ['GET'], format: 'html')]
    #[Route('/blog/rss.xml', name: RouteBlog::RSS->value, methods: ['GET'], format: 'xml')]
    public function __invoke(
        string $_format,
        Request $request,
        PostRepository $postRepository,
    ): Response {
        $latestPost = $postRepository->getOneLatestPublished();

        $response = $this->createCacheableResponse(
            etag: [$latestPost],
            lastModified: $latestPost->getPublishedAt(),
            sharedMaxAge: 60 * 60 * 24 * 14,
        );

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render(sprintf('blog/home.%s.twig', $_format), [
            'posts' => $postRepository->findLatestPublished(),
        ], $response);
    }
}
