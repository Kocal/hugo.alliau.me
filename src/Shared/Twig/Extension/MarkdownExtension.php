<?php

namespace App\Shared\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    public function getFilters(): iterable
    {
        yield new TwigFilter('markdown', [MarkdownRuntime::class, 'render'], [
            'is_safe' => ['html'],
        ]); 
    }
}
