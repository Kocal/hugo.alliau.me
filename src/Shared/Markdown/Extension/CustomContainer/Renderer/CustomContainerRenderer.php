<?php

namespace App\Shared\Markdown\Extension\CustomContainer\Renderer;

use App\Shared\Markdown\Extension\CustomContainer\Node\CustomContainer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;

final class CustomContainerRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!($node instanceof CustomContainer)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }

        $type = $node->getType();
        $title = $node->getTitle();

        return <<<HTML
            <div class="    custom-container custom-container--$type">
                <p class="custom-container__title">$title</p>
                <div class="prose max-w-none">{$childRenderer->renderNodes($node->children())}</div>
            </div>
HTML;
    }
}