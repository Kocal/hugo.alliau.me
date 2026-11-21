<?php

declare(strict_types=1);

namespace App\Blog\Domain\Data;

enum Route: string
{
    case HOME = 'app.blog.home';

    case RSS = 'app.blog.rss';

    case POST_VIEW = 'app.blog.posts.view';

    case TAG_LIST = 'app.blog.tags.list';

    case TAG_VIEW = 'app.blog.tags.view';

    case ADMIN_POST_IMAGE_UPLOAD = 'app.blog.admin.post_images.upload';
}
