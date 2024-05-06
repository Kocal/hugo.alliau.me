<?php

namespace App\Blog\Infrastructure\Http\Tag;

use App\Blog\Domain\Repository\PostRepository;
use App\Domain\Routing\ValueObject\RouteName;
use App\Http\Cache\CacheMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route("/blog/tags", name: RouteName::BLOG_TAG_LIST, methods: ['GET'])]
    public function __invoke(
        Request $request,
        PostRepository $postRepository
    ): Response {
        $response = new Response();
        $response->setEtag(self::computeEtag('tags:list'));
        $response->setLastModified($postRepository->getOneLatestPublished()->getPublishedAt());
        $response->setMaxAge(60 * 60 * 24 * 30);
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render("blog/tags/list/index.html.twig", [
            'tags' => $postRepository->findTagsAndOccurrences(),
        ], $response);
    }
}
