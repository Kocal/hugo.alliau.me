<?php

declare(strict_types=1);

namespace App\Shared\Domain\Markdown;

interface MarkdownConverter
{
    public function __invoke(string $input): MarkdownDocument;
}
