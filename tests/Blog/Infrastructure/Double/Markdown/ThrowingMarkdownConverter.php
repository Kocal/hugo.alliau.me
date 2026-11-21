<?php

declare(strict_types=1);

namespace App\Tests\Blog\Infrastructure\Double\Markdown;

use App\Shared\Domain\Markdown\MarkdownConverter;
use App\Shared\Domain\Markdown\MarkdownDocument;

final class ThrowingMarkdownConverter implements MarkdownConverter
{
    public int $calls = 0;

    public function __construct(
        private readonly ?\Throwable $exception = null,
    ) {
    }

    #[\Override]
    public function __invoke(string $input): MarkdownDocument
    {
        ++$this->calls;

        if ($this->exception instanceof \Throwable) {
            throw $this->exception;
        }

        return new MarkdownDocument($input);
    }
}
