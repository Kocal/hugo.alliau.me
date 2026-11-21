<?php

declare(strict_types=1);

namespace App\Blog\Application\Markdown;

use App\Blog\Application\Image\ImageDimensions;
use App\Blog\Application\Image\ImageProbe;

final readonly class ImageReferenceRewriter
{
    private const string PATTERN = '~!\[(?P<alt>[^\]]*)\]\((?P<url>(?:https://cdn\.kocal\.fr/image/)?https://minio\.kocal\.fr/hugo\.alliau\.me/(?P<key>[^)\s?"]+)(?:\?[^)\s"]*)?)(?P<title>\s+"[^"]*")?\)(?P<attrs>\{[^}]*\})?~';

    public function __construct(
        private ImageProbe $probe,
    ) {
    }

    public function rewrite(string $markdown): RewriteResult
    {
        $replacements = [];
        $missingKeys = [];
        $unreadableKeys = [];

        $content = preg_replace_callback(
            self::PATTERN,
            function (array $match) use (&$replacements, &$missingKeys, &$unreadableKeys): string {
                $attributes = $match['attrs'] ?? '';
                $key = $match['key'];

                if (! $this->hasAttribute($attributes, 'width') || ! $this->hasAttribute($attributes, 'height')) {
                    $dimensions = $this->probe->probe($key);

                    if ($dimensions === null) {
                        $missingKeys[] = $key;

                        return $match[0];
                    }

                    if ($dimensions === false) {
                        $unreadableKeys[] = $key;

                        return $match[0];
                    }

                    $attributes = $this->withDimensions($attributes, $dimensions);
                }

                $replacements[] = [
                    'from' => $match['url'],
                    'to' => $key,
                    'attributes' => $attributes,
                ];

                return \sprintf('![%s](%s%s)%s', $match['alt'], $key, $match['title'] ?? '', $attributes);
            },
            $markdown,
        );

        return new RewriteResult($content ?? $markdown, $replacements, $missingKeys, $unreadableKeys);
    }

    private function hasAttribute(string $attributes, string $name): bool
    {
        return preg_match(\sprintf('~(?:^|[\s{])%s=~', $name), $attributes) === 1;
    }

    private function attributeValue(string $attributes, string $name): ?int
    {
        return preg_match(\sprintf('~(?:^|[\s{])%s=(\d+)~', $name), $attributes, $m) === 1 ? (int) $m[1] : null;
    }

    private function withDimensions(string $attributes, ImageDimensions $dimensions): string
    {
        $width = $this->attributeValue($attributes, 'width');
        $height = $this->attributeValue($attributes, 'height');

        if ($width !== null) {
            $added = \sprintf('height=%d', max(1, (int) round($width * $dimensions->height / $dimensions->width)));
        } elseif ($height !== null) {
            $added = \sprintf('width=%d', max(1, (int) round($height * $dimensions->width / $dimensions->height)));
        } else {
            $added = \sprintf('width=%d height=%d', $dimensions->width, $dimensions->height);
        }

        $existing = trim(substr($attributes, 1, -1));

        return $existing === '' ? '{' . $added . '}' : \sprintf('{%s %s}', $existing, $added);
    }
}
