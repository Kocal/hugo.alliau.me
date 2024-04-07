<?php

namespace App\Http\Controller\Blog\Tag;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Routing\ValueObject\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ViewController extends AbstractController
{
    #[Route("/blog/tags/{tag}", name: RouteName::BLOG_TAG_VIEW, methods: ['GET'])]
    public function __invoke(
        string $tag,
        PostRepository $postRepository
    ): Response {
        return $this->render("blog/tags/view/index.html.twig", [
            'tag' => $tag,
            'tags' => $postRepository->findTagsAndOccurrences(),
            'posts' => $postRepository->findLatestPublished(
                tags: [$tag]
            ),
        ]);
    }
}
