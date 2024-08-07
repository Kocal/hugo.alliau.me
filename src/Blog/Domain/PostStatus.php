<?php

declare(strict_types=1);

namespace App\Blog\Domain;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}
