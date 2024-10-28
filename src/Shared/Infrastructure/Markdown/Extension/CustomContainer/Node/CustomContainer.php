<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Markdown\Extension\CustomContainer\Node;

use League\CommonMark\Node\Block\AbstractBlock;

final class CustomContainer extends AbstractBlock
{
    public function __construct(
        private readonly string $type,
        private readonly string|null $title,
    ) {
        parent::__construct();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
