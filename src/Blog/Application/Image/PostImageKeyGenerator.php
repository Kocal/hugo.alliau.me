<?php

declare(strict_types=1);

namespace App\Blog\Application\Image;

use Symfony\Component\String\Slugger\AsciiSlugger;

final readonly class PostImageKeyGenerator
{
    /**
     * @param \Closure(string): bool $exists
     */
    public function generate(string $slug, string $clientBasename, string $extension, \Closure $exists): string
    {
        $name = new AsciiSlugger()->slug($clientBasename)->lower()->toString();

        if ($name === '') {
            $name = 'image';
        }

        $key = \sprintf('posts-assets/%s/%s.%s', $slug, $name, $extension);
        $suffix = 2;

        while ($exists($key)) {
            $key = \sprintf('posts-assets/%s/%s-%d.%s', $slug, $name, $suffix, $extension);
            ++$suffix;
        }

        return $key;
    }
}
