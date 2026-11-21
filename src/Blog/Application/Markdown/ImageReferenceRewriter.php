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
                $key = $match['key'];
                $dimensions = $this->probe->probe($key);

                if ($dimensions === null) {
                    $missingKeys[] = $key;

                    return $match[0];
                }

                if ($dimensions === false) {
                    $unreadableKeys[] = $key;

                    return $match[0];
                }

                $attributes = $this->withDimensions($match['attrs'] ?? '', $dimensions);

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

    private const string DIMENSION_ATTRIBUTE = '~(?<=^|[\s{:])(?P<name>width|height)=["\']?(?P<value>\d+)["\']?~';

    /**
     * The author's width is a display size: it is kept (capped to the real width) and the height follows the real ratio.
     */
    private function withDimensions(string $attributes, ImageDimensions $dimensions): string
    {
        $authored = [];
        if (preg_match_all(self::DIMENSION_ATTRIBUTE, $attributes, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $m) {
                $authored[$m['name']] = (int) $m['value'];
            }
        }

        $width = $authored['width'] ?? null;
        if ($width === null && isset($authored['height'])) {
            $width = (int) round($authored['height'] * $dimensions->width / $dimensions->height);
        }

        $width = max(1, min($width ?? $dimensions->width, $dimensions->width));
        $height = max(1, (int) round($width * $dimensions->height / $dimensions->width));

        $inner = trim(substr($attributes, 1, -1));
        $prefix = str_starts_with($inner, ':') ? ':' : '';
        $others = preg_split('~\s+~', trim(preg_replace(self::DIMENSION_ATTRIBUTE, '', ltrim($inner, ':')) ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return \sprintf('{%s%s}', $prefix, implode(' ', [...$others, \sprintf('width=%d', $width), \sprintf('height=%d', $height)]));
    }
}
