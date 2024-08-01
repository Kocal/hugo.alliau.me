<?php

namespace App\Shared\Markdown\Extension\CustomContainer\Renderer;

use App\Shared\Markdown\Extension\CustomContainer\Node\CustomContainer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

final class CustomContainerRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! ($node instanceof CustomContainer)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }

        $type = $node->getType();
        $title = $node->getTitle();

        return <<<HTML
            <div class="CustomContainer CustomContainer--$type">
                <p class="CustomContainer__Title">$title</p>
                <div class="prose max-w-none">{$childRenderer->renderNodes($node->children())}</div>
            </div>
HTML;
    }
}
