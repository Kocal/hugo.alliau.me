<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\League\CommonMark\Markdown\Extension\TempestHighlight\Renderer;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\WebTheme;
use function Symfony\Component\String\s;

final readonly class CodeBlockRenderer implements NodeRendererInterface
{
    /**
     * @param (\Closure(): Highlighter) $getHighlighter
     */
    public function __construct(
        private \Closure $getHighlighter,
    ) {
    }

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

        $highlighter = ($this->getHighlighter)();

        $theme = $highlighter->getTheme();
        $parsed = $highlighter->parse($node->getLiteral(), $language);
        $output = $theme instanceof WebTheme
            ? $theme->preBefore($highlighter) . $parsed . $theme->preAfter($highlighter)
            : '<pre data-lang="' . $language . '">' . $parsed . '</pre>';

        $output = s($output);
        // Add a tabindex to the pre tag to make it focusable
        $output = $output->replace('<pre', '<pre tabindex="0"');

        return '<div class="Terminal">'
            . ($filename !== null && $filename !== '' && $filename !== '0' ? '<div class="Terminal__Header"><span title="' . $filename . '">' . $filename . '</span></div>' : '')
            . '<div class="Terminal__Body">'
            . $output
            . '</div></div>';
    }
}
