<?php

namespace App\Http\Controller\Blog;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Routing\ValueObject\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route("/blog", name: RouteName::BLOG_HOME)]
    public function __invoke(
        PostRepository $postRepository
    ): Response
    {
        return $this->render("blog/home.html.twig", [
            'posts' => $postRepository->findLatest(),
        ]);
    }
}
