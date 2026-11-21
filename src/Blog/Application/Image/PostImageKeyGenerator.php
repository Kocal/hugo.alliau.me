<?php

declare(strict_types=1);

namespace App\Blog\Application\Image;

use Symfony\Component\String\Slugger\AsciiSlugger;

final readonly class PostImageKeyGenerator
{
    private AsciiSlugger $slugger;

    public function __construct()
    {
        $this->slugger = new AsciiSlugger();
    }

    /**
     * @param \Closure(string): bool $exists
     */
    public function generate(string $slug, string $clientBasename, string $extension, \Closure $exists): string
    {
        $name = $this->slugger->slug($clientBasename)->lower()->toString();

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
