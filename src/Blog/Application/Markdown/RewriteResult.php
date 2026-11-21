<?php

declare(strict_types=1);

namespace App\Blog\Application\Markdown;

final readonly class RewriteResult
{
    /**
     * @param list<array{from: string, to: string, attributes: string}> $replacements
     * @param list<string>                          $missingKeys
     * @param list<string>                          $unreadableKeys
     */
    public function __construct(
        public string $content,
        public array $replacements,
        public array $missingKeys,
        public array $unreadableKeys,
    ) {
    }
}
