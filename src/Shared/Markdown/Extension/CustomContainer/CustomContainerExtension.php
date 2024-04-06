<?php

namespace App\Shared\Markdown\Extension\CustomContainer;

use App\Shared\Markdown\Extension\CustomContainer\Node\CustomContainer;
use App\Shared\Markdown\Extension\CustomContainer\Parser\CustomContainerBlockParser;
use App\Shared\Markdown\Extension\CustomContainer\Renderer\CustomContainerRenderer;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * @see https://vitepress.dev/guide/markdown#custom-containers
 */
final class CustomContainerExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addBlockStartParser(CustomContainerBlockParser::createBlockStartParser())
            ->addRenderer(CustomContainer::class, new CustomContainerRenderer())
        ;
    }
}
