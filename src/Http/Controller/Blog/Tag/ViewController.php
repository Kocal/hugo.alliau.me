<?php

namespace App\Http\Controller\Blog\Tag;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Routing\ValueObject\RouteName;
use App\Http\Cache\CacheMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ViewController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route("/blog/tags/{tag}", name: RouteName::BLOG_TAG_VIEW, methods: ['GET'])]
    public function __invoke(
        Request $request,
        string $tag,
        PostRepository $postRepository
    ): Response {
        $posts = $postRepository->findLatestPublished(
            tags: [$tag]
        );

        if ($posts === []) {
            throw $this->createNotFoundException();
        }

        $response = new Response();
        $response->setEtag(self::computeEtag('tags:view:' . $tag));
        $response->setLastModified(current($posts)->getPublishedAt());
        $response->setMaxAge(60 * 60 * 24 * 30);
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render("blog/tags/view/index.html.twig", [
            'tag' => $tag,
            'tags' => $postRepository->findTagsAndOccurrences(),
            'posts' => $posts,
        ], $response);
    }
}
