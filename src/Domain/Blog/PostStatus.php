<?php

namespace App\Domain\Blog;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}