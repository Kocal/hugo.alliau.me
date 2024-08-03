<?php

namespace App\Blog\Infrastructure\Symfony\Twig\Components;

use App\Blog\Domain\Post as BlogPost;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PostMeta
{
    public BlogPost $post;
}
