<?php

namespace App\Shared\Twig\Components;

use App\Domain\Blog\Post as BlogPost;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Post
{
    public BlogPost $post;
}
