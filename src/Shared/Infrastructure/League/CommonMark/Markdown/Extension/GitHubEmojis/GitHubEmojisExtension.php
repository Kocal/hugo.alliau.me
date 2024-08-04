<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\League\CommonMark\Markdown\Extension\GitHubEmojis;

use App\Shared\Infrastructure\League\CommonMark\Markdown\Extension\GitHubEmojis\Parser\GitHubEmojisInlineParser;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class GitHubEmojisExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addInlineParser(new GitHubEmojisInlineParser());
    }
}
