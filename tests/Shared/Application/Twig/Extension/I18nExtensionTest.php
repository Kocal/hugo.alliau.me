<?php

declare(strict_types=1);

namespace App\Tests\Shared\Application\Twig\Extension;

use App\Shared\Application\Twig\Extension\I18nExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

#[CoversClass(I18nExtension::class)]
final class I18nExtensionTest extends TestCase
{
    private function extensionWith(string $message): I18nExtension
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', [
            'msg' => $message,
        ], 'en', 'messages');

        return new I18nExtension($translator);
    }

    public function testItReplacesPlaceholdersWithAnchors(): void
    {
        $extension = $this->extensionWith('See my <0>CV</0> and my <1>blog</1>.');

        $html = $extension->tHtml('msg', [
            [
                'href' => '/cv',
            ],
            [
                'href' => '/blog',
                'class' => 'link',
            ],
        ]);

        $this->assertSame('See my <a href="/cv">CV</a> and my <a href="/blog" class="link">blog</a>.', $html);
    }

    public function testItEscapesLinkTextAndAttributes(): void
    {
        $extension = $this->extensionWith('Danger <0>a & b</0>');

        $html = $extension->tHtml('msg', [
            [
                'href' => '/x?a=1&b="2"',
            ],
        ]);

        $this->assertSame('Danger <a href="/x?a=1&amp;b=&quot;2&quot;">a &amp; b</a>', $html);
    }

    public function testItLeavesTextWithoutPlaceholdersUnchanged(): void
    {
        $extension = $this->extensionWith('Just text');

        $this->assertSame('Just text', $extension->tHtml('msg'));
    }

    public function testItReturnsTheFallbackRouteForASingleLocaleRoute(): void
    {
        $extension = new I18nExtension(new Translator('en'), [
            [
                'route' => 'app.blog.posts.view',
                'fallback_route' => 'app.blog.home',
            ],
            [
                'route' => 'app.recipes.view',
                'fallback_route' => 'app.recipes.list',
            ],
        ]);

        $this->assertSame('app.blog.home', $extension->singleLocaleFallbackRoute('app.blog.posts.view'));
        $this->assertSame('app.recipes.list', $extension->singleLocaleFallbackRoute('app.recipes.view'));
    }

    public function testItReturnsNullForARouteThatExistsInEveryLocale(): void
    {
        $extension = new I18nExtension(new Translator('en'), [
            [
                'route' => 'app.blog.posts.view',
                'fallback_route' => 'app.blog.home',
            ],
        ]);

        $this->assertNull($extension->singleLocaleFallbackRoute('app.blog.home'));
    }

    public function testItReturnsNullWhenNoSingleLocaleRouteIsConfigured(): void
    {
        $extension = $this->extensionWith('Just text');

        $this->assertNull($extension->singleLocaleFallbackRoute('app.blog.home'));
    }
}
