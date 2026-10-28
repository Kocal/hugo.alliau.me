<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Symfony\Controller\Tag;

use App\Blog\Domain\Data\Route as RouteBlog;
use App\Blog\Domain\Repository\PostRepository;
use App\Shared\Infrastructure\Symfony\HttpCache\CacheableResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListController extends AbstractController
{
    use CacheableResponseTrait;

    #[Route(path: [
        'en' => '/blog/tags',
        'fr' => '/fr/blog/tags',
    ], name: RouteBlog::TAG_LIST->value, options: [
        'sitemap' => true,
    ], methods: ['GET'])]
    public function __invoke(
        Request $request,
        PostRepository $postRepository
    ): Response {
        $response = $this->createCacheableResponse(
            etag: ['tags:list'],
            lastModified: $postRepository->getOneLatestPublished()
                ->getPublishedAt(),
            sharedMaxAge: 60 * 60 * 24 * 30,
        );

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render('blog/tags/list/index.html.twig', [
            'tags' => $postRepository->findTagsAndOccurrences(),
        ], $response);
    }
}
