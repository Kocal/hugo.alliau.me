<?php

namespace App\Shared\Twig\Extension;

use App\Shared\Markdown\MarkdownConverter;
use Twig\Extension\RuntimeExtensionInterface;

class MarkdownRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private MarkdownConverter $markdownConverter,
    ) {  
    }

    public function render(string $input): string 
    {
        return ($this->markdownConverter)($input);
    }
}
