<?php

namespace App\Http\Controller\Blog\Post;

use App\Domain\Blog\Post;
use App\Domain\Routing\ValueObject\RouteName;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ViewController extends AbstractController
{
    #[Route("/blog/posts/{slug}", name: RouteName::BLOG_POST_VIEW)]
    public function __invoke(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Post $post,
        Request $request,
    ): Response
    {
        return $this->render("blog/posts/view/index.html.twig", [
            'post' => $post,
        ]);
    }
}
