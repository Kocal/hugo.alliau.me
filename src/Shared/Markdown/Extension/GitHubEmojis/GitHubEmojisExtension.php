<?php

namespace App\Shared\Markdown\Extension\GitHubEmojis;

use App\Shared\Markdown\Extension\GitHubEmojis\Parser\GitHubEmojisInlineParser;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class GitHubEmojisExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addInlineParser(new GitHubEmojisInlineParser());
    }
}
