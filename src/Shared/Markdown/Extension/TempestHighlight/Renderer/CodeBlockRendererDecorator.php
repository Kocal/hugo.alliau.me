<?php

declare(strict_types=1);

namespace App\Shared\Markdown\Extension\TempestHighlight\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Tempest\Highlight\CommonMark\CodeBlockRenderer;
use function Symfony\Component\String\s;

final class CodeBlockRendererDecorator implements NodeRendererInterface
{
    /**
     * https://regex101.com/r/KowUzi/1
     */
    private static $RE_REMOVE_GUTTER_END_SPACE = '/<span class="hl-gutter[^"]*">\s*\d+<\/span>\s{1}/';

    public function __construct(
        private CodeBlockRenderer $codeBlockRenderer,
    ) {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        $output = $this->codeBlockRenderer->render($node, $childRenderer);

        // Replace the gutter's end space with nothing, as we don't want it to be selectable
        $output = s($output)->replaceMatches(self::$RE_REMOVE_GUTTER_END_SPACE, static fn (array $matches) => s($matches[0])->trim()->toString());

        // Add a tabindex to the pre tag to make it focusable
        $output = $output->replace('<pre', '<pre tabindex="0"');

        return $output->toString();
    }
}
