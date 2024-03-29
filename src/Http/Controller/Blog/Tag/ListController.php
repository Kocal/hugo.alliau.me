<?php

namespace App\Http\Controller\Blog\Tag;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Routing\ValueObject\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/blog/tags", name: RouteName::BLOG_TAG_LIST)]
final class ListController extends AbstractController
{
    public function __invoke(
        PostRepository $postRepository
    ): Response
    {
        return $this->render("blog/tags/list/index.html.twig", [
            'tags' => $postRepository->findTagsAndOccurrences(),
        ]);
    }
}
