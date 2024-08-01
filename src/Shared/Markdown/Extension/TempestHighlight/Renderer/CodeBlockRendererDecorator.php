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
     * https://regex101.com/r/5nJBlZ/1
     */
    private const string RE_REMOVE_GUTTER_END_SPACE = '/<span class="hl-gutter[^"]*">[^<]+<\/span>\s{1}/';

    /**
     * @param (\Closure(): CodeBlockRenderer) $getCodeBlockRenderer
     */
    public function __construct(
        private \Closure $getCodeBlockRenderer,
    ) {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        $output = ($this->getCodeBlockRenderer)()->render($node, $childRenderer);

        $output = s($output);

        // Replace the gutter's end space with nothing, as we don't want it to be selectable
        $output = $output->replaceMatches(self::RE_REMOVE_GUTTER_END_SPACE, static fn (array $matches) => s($matches[0])->trim()->toString());

        // Add a tabindex to the pre tag to make it focusable
        $output = $output->replace('<pre', '<pre tabindex="0"');

        return $output->toString();
    }
}
