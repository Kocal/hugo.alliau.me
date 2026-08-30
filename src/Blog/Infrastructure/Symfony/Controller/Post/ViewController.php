<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Symfony\Controller\Post;

use App\Blog\Domain\Data\Post;
use App\Blog\Domain\Data\PostStatus;
use App\Blog\Domain\Data\Route as RouteBlog;
use App\Shared\Domain\HttpCache\CacheMethodsTrait;
use App\Shared\Domain\Markdown\MarkdownConverter;
use Psr\Link\EvolvableLinkInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ViewController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route(path: [
        'en' => '/blog/posts/{slug}',
        'fr' => '/fr/blog/posts/{slug}',
    ], name: RouteBlog::POST_VIEW->value, methods: ['GET'])]
    public function __invoke(
        #[MapEntity(mapping: [
            'slug' => 'slug',
        ])]
        Post $post,
        Request $request,
        MarkdownConverter $markdownConverter,
    ): Response {
        $isPreview = $request->query->has('preview');

        if ($post->getStatus() === PostStatus::DRAFT && ! $isPreview) {
            throw $this->createNotFoundException();
        }

        if (! $isPreview && $request->getLocale() !== $post->getLocale()->value) {
            return $this->redirectToRoute(RouteBlog::POST_VIEW->value, [
                'slug' => $post->getSlug(),
                '_locale' => $post->getLocale()
                    ->value,
            ], Response::HTTP_MOVED_PERMANENTLY);
        }

        $response = new Response();
        $response->setEtag(self::computeEtag($post));
        $response->setLastModified($post->getPublishedAt());
        $response->setMaxAge(60 * 60 * 24 * 30);
        $response->setPublic();

        if ($post->getStatus() === PostStatus::DRAFT) {
            $response->setPrivate();
            $response->setMaxAge(0);
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        if ($response->isNotModified($request)) {
            return $response;
        }

        $markdownDocument = ($markdownConverter)($post->getContent());

        foreach ($markdownDocument->webLinks as $webLink) {
            if ($webLink instanceof EvolvableLinkInterface) {
                $webLink = $webLink->withRel('preload');
            }

            $this->addLink($request, $webLink);
        }

        return $this->render('blog/posts/view/index.html.twig', [
            'post' => $post,
            'rendered_content' => $markdownDocument->renderedContent,
        ], $response);
    }
}
