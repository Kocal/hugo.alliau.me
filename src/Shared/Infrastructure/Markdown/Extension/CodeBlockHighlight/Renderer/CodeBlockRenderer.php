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

        $codeOriginal = trim($node->getLiteral());
        ['has_diff' => $hasDiff, 'code' => $codeClean] = $this->cleanCodeFromDiff($codeOriginal);

        $output = sprintf(
            '<pre class="%s" data-controller="code-highlight" data-code-highlight-code-value="%s" data-code-highlight-lang-value="%s"><code>%s</code></pre>',
            implode(' ', ['shiki', $hasDiff ? 'has-diff' : '']),
            htmlspecialchars($codeOriginal),
            $language,
            htmlspecialchars((string) $codeClean),
        );

        return sprintf(
            '<div class="Terminal" tabindex="0">%s<div class="Terminal__Body">%s</div></div>',
            $filename !== null && $filename !== '' && $filename !== '0'
                ? sprintf('<div class="Terminal__Header"><span title="%s">%s</span></div>', $filename, $filename)
                : '',
            $output
        );
    }

    /**
     * @return array{ has_diff: bool, code: string }
     */
    private function cleanCodeFromDiff(string $code): array
    {
        $markerAdd = '[!code ++]';
        $markerRemove = '[!code --]';
        $comments = ['//', '#'];

        if (! str_contains($code, $markerAdd) && ! str_contains($code, $markerRemove)) {
            return [
                'has_diff' => false,
                'code' => $code,
            ];
        }

        foreach ($comments as $comment) {
            $code = str_replace($comment . ' ' . $markerAdd, '', $code);
            $code = str_replace($comment . ' ' . $markerRemove, '', $code);
        }

        return [
            'has_diff' => true,
            'code' => $code,
        ];
    }
}
