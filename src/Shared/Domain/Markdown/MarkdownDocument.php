<?php

declare(strict_types=1);

namespace App\Shared\Domain\Markdown;

use Psr\Link\EvolvableLinkInterface;
use Psr\Link\LinkInterface;

final readonly class MarkdownDocument
{
    public function __construct(
        public string $renderedContent,
        public ?string $renderedToc,
        /**
         * @var array<LinkInterface|EvolvableLinkInterface>
         */
        public array $webLinks = []
    ) {
    }
}
