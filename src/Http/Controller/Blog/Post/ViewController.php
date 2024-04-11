<?php

namespace App\Http\Controller\Blog\Post;

use App\Domain\Blog\Post;
use App\Domain\Routing\ValueObject\RouteName;
use App\Http\Cache\CacheMethodsTrait;
use App\Shared\Markdown\MarkdownConverter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ViewController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route("/blog/posts/{slug}", name: RouteName::BLOG_POST_VIEW, methods: ['GET'])]
    public function __invoke(
        #[MapEntity(mapping: [
            'slug' => 'slug',
        ])]
        Post $post,
        Request $request,
        MarkdownConverter $markdownConverter,
    ): Response {
        $response = new Response();
        $response->setEtag(self::computeEtag($post));
        $response->setLastModified($post->getPublishedAt());
        $response->setMaxAge(60 * 60 * 24 * 30);
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        [
            'rendered_content' => $renderedContent,
            'rendered_toc' => $renderedToc,
            'web_links' => $webLinks,
        ] = ($markdownConverter)($post->getContent());


        foreach ($webLinks as $webLink) {
            $webLink = $webLink->withRel('preload');

            $this->addLink($request, $webLink);
        }

        return $this->render("blog/posts/view/index.html.twig", [
            'post' => $post,
            'rendered_content' => $renderedContent,
            'rendered_toc' => $renderedToc,
        ], $response);
    }
}
