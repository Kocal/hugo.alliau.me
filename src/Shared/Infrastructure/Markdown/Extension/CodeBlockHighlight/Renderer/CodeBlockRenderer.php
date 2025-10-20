<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Markdown\Extension\CodeBlockHighlight\Renderer;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

final readonly class CodeBlockRenderer implements NodeRendererInterface
{
    #[\Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string|null
    {
        if (! $node instanceof FencedCode) {
            throw new InvalidArgumentException('Block must be instance of ' . FencedCode::class);
        }

        $infoWords = $node->getInfoWords();
        preg_match('/^(?<language>[\w]+)/', $infoWords[0] ?? 'txt', $matches);
        $language = $matches['language'] ?? 'txt';
        $filename = trim($infoWords[1] ?? '', '[]') !== '' && trim($infoWords[1] ?? '', '[]') !== '0' ? trim($infoWords[1] ?? '', '[]') : null;

        $output = '<pre data-controller="code-highlight" tabindex="0" data-lang="' . $language . '">' . htmlspecialchars(trim($node->getLiteral())) . '</pre>';

        return '<div class="Terminal">'
            . ($filename !== null && $filename !== '' && $filename !== '0' ? '<div class="Terminal__Header"><span title="' . $filename . '">' . $filename . '</span></div>' : '')
            . '<div class="Terminal__Body">' . $output . '</div>'
            . '</div>';
    }
}
