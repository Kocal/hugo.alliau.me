<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Markdown;

use App\Shared\Infrastructure\Markdown\Extension\Image\Exception\MissingImageDimensionsException;
use App\Shared\Infrastructure\Markdown\Extension\Image\Renderer\ObjectKeyImageRenderer;
use App\Shared\Infrastructure\Markdown\LeagueCommonMarkMarkdownConverter;
use App\Tests\Shared\Infrastructure\Double\Image\QueryStringProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\UX\Image\ImagePresets;
use Symfony\UX\Image\ImageUrlGenerator;
use Symfony\UX\Image\Renderer\ImageRenderer;
use Symfony\UX\Image\Renderer\LayoutResolver;

#[CoversClass(LeagueCommonMarkMarkdownConverter::class)]
#[CoversClass(ObjectKeyImageRenderer::class)]
#[CoversClass(MissingImageDimensionsException::class)]
final class LeagueCommonMarkMarkdownConverterTest extends TestCase
{
    public function testObjectKeyWithDimensionsRendersResponsiveImage(): void
    {
        $converter = $this->converter();

        $document = $converter('![Config](posts-assets/a/shot.png "Coolify"){width=1200 height=630 fetchpriority=high}');

        $html = $document->renderedContent;
        $src = 'https://img.test/posts-assets/a/shot.png?w=1200&amp;h=630&amp;f=webp&amp;q=80';
        $this->assertStringContainsString('src="' . $src . '"', $html);
        $this->assertStringContainsString('srcset="', $html);
        $this->assertStringContainsString('1200w', $html);
        $this->assertStringContainsString('2400w', $html);
        $this->assertStringContainsString('sizes="(min-width: 768px) 768px, 100vw"', $html);
        $this->assertStringContainsString('width="1200"', $html);
        $this->assertStringContainsString('height="630"', $html);
        $this->assertStringContainsString('loading="eager"', $html);
        $this->assertStringContainsString('fetchpriority="high"', $html);
        $this->assertStringContainsString('alt="Config"', $html);
        $this->assertStringContainsString('title="Coolify"', $html);
        $this->assertMatchesRegularExpression('{style="[^"]*aspect-ratio: 1200 / 630}', $html);
        $this->assertCount(1, $document->webLinks);
        $link = $document->webLinks[0];
        $this->assertSame(html_entity_decode($src), $link->getHref());
        $this->assertArrayHasKey('imagesrcset', $link->getAttributes());
        $this->assertArrayHasKey('imagesizes', $link->getAttributes());
        $this->assertStringContainsString('srcset="' . htmlspecialchars($link->getAttributes()['imagesrcset']) . '"', $html);
    }

    public function testObjectKeyNarrowerThanContentUsesItsOwnWidthInSizes(): void
    {
        $converter = $this->converter();

        $document = $converter('![x](posts-assets/a/b.png){width=400 height=300}');

        $html = $document->renderedContent;
        $this->assertStringContainsString('sizes="(min-width: 400px) 400px, 100vw"', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringNotContainsString('fetchpriority', $html);
    }

    public function testObjectKeyWithoutDimensionsThrows(): void
    {
        $converter = $this->converter();

        $this->expectException(MissingImageDimensionsException::class);
        $this->expectExceptionMessage('posts-assets/a/b.png');
        $converter('![x](posts-assets/a/b.png)');
    }

    public function testObjectKeyWithOnlyAWidthThrows(): void
    {
        $converter = $this->converter();

        $this->expectException(MissingImageDimensionsException::class);
        $this->expectExceptionMessage('posts-assets/a/b.png');
        $converter('![x](posts-assets/a/b.png){width=400}');
    }

    public function testAbsoluteAndRootRelativeUrlsAreLeftToTheDefaultRenderer(): void
    {
        $converter = $this->converter();

        $document = $converter("![x](https://example.com/i.png)\n\n![y](/build/i.png)");

        $this->assertStringContainsString('<img src="https://example.com/i.png" alt="x" />', $document->renderedContent);
        $this->assertStringContainsString('<img src="/build/i.png" alt="y" />', $document->renderedContent);
    }

    public function testEmptyAndFragmentUrlsAreLeftToTheDefaultRenderer(): void
    {
        $converter = $this->converter();

        $document = $converter("![x]()\n\n![y](#anchor)");

        $this->assertStringContainsString('<img src="" alt="x" />', $document->renderedContent);
        $this->assertStringContainsString('<img src="#anchor" alt="y" />', $document->renderedContent);
    }

    public function testDotSegmentsAreLeftToTheDefaultRenderer(): void
    {
        $converter = $this->converter();

        $document = $converter('![x](../secret.png)');

        $this->assertStringContainsString('<img src="../secret.png" alt="x" />', $document->renderedContent);
    }

    public function testAbsoluteUrlWithHighPriorityStillProducesAPreloadLink(): void
    {
        $converter = $this->converter();

        $document = $converter('![x](https://example.com/i.png){fetchpriority=high}');

        $this->assertCount(1, $document->webLinks);
        $this->assertSame('https://example.com/i.png', $document->webLinks[0]->getHref());
        $this->assertArrayNotHasKey('imagesrcset', $document->webLinks[0]->getAttributes());
    }

    public function testPriorityImagesDoNotLeakIntoTheNextDocument(): void
    {
        $converter = $this->converter();
        $converter('![Config](posts-assets/a/shot.png "Coolify"){width=1200 height=630 fetchpriority=high}');

        $document = $converter('![x](posts-assets/a/b.png){width=10 height=10}');

        $this->assertCount(0, $document->webLinks);
    }

    public function testPriorityImagesDoNotLeakAfterAFailedConversion(): void
    {
        $converter = $this->converter();
        try {
            $converter('![a](posts-assets/a/a.png){width=10 height=10 fetchpriority=high} ![b](posts-assets/a/b.png)');
            $this->fail('The first conversion should have thrown.');
        } catch (MissingImageDimensionsException) {
        }

        $document = $converter('![c](posts-assets/a/c.png){width=10 height=10}');

        $this->assertCount(0, $document->webLinks);
    }

    private function converter(): LeagueCommonMarkMarkdownConverter
    {
        $provider = new QueryStringProvider();
        $presets = new ImagePresets([
            'post' => [
                'format' => 'webp',
                'quality' => 80,
            ],
        ]);
        $urlGenerator = new ImageUrlGenerator($provider, presets: $presets);
        $renderer = new ImageRenderer($provider, new LayoutResolver(), ['webp'], $urlGenerator, $presets);
        $twig = new \Twig\Environment(new \Twig\Loader\ArrayLoader([]));

        return new LeagueCommonMarkMarkdownConverter($twig, $renderer);
    }
}
