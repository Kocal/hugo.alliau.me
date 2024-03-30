<?php

namespace App\Shared\Markdown\Extension\GitHubEmojis\Parser;

use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use function Symfony\Component\String\s;

final class GitHubEmojisInlineParser implements InlineParserInterface
{
    private static array $emojis = [];

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex(':[a-z0-9\+\-_]+:');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $inlineContext->getCursor()->advanceBy($inlineContext->getFullMatchLength());

        $emojiName = s($inlineContext->getFullMatch())->trim(':')->toString();

        $inlineContext->getContainer()->appendChild(
            new Text(
                self::getEmojis()[$emojiName]
            )
        );

        return true;
    }

    private static function getEmojis(): array
    {
        if (self::$emojis === []) {
            self::$emojis = require __DIR__ . '/../data/emojis.php';
        }

        return self::$emojis;
    }
}