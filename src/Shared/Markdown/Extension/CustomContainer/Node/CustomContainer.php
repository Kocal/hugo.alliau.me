<?php

namespace App\Shared\Markdown\Extension\CustomContainer\Node;

use League\CommonMark\Node\Block\AbstractBlock;

final class CustomContainer extends AbstractBlock
{
    public function __construct(
        private string $type,
        private string|null $title,
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
