<?php

namespace App\Shared\Markdown\Extension\GitHubEmojis\Parser;

use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

final class GitHubEmojisInlineParser implements InlineParserInterface
{
    /**
     * @var array<string, string>
     */
    private static array $emojis = [];

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex(':[a-z0-9\+\-_]+:');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $inlineContext->getCursor()->advanceBy($inlineContext->getFullMatchLength());

        $match = $inlineContext->getFullMatch();

        $inlineContext->getContainer()->appendChild(
            new Text(
                self::getEmojis()[str_replace(':', '', $match)] ?? $match,
            )
        );

        return true;
    }

    /**
     * @return array<string, string>
     */
    private static function getEmojis(): array
    {
        if (self::$emojis === []) {
            self::$emojis = require __DIR__ . '/../data/emojis.php';
        }

        return self::$emojis;
    }
}
