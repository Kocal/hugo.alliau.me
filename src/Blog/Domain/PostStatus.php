<?php

namespace App\Blog\Domain;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}
