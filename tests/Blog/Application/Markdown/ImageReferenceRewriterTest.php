<?php

declare(strict_types=1);

namespace App\Tests\Blog\Application\Markdown;

use App\Blog\Application\Image\ImageDimensions;
use App\Blog\Application\Markdown\ImageReferenceRewriter;
use App\Blog\Application\Markdown\RewriteResult;
use App\Tests\Blog\Application\Double\InMemoryImageProbe;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageReferenceRewriter::class)]
#[CoversClass(RewriteResult::class)]
final class ImageReferenceRewriterTest extends TestCase
{
    private const string CDN = 'https://cdn.kocal.fr/image/https://minio.kocal.fr/hugo.alliau.me/';

    public function testCdnUrlWithQueryBecomesKeyWithDimensions(): void
    {
        $rewriter = new ImageReferenceRewriter(new InMemoryImageProbe([
            'posts-assets/a/shot.png' => new ImageDimensions(1200, 630),
        ]));

        $result = $rewriter->rewrite('![Shot](' . self::CDN . 'posts-assets/a/shot.png?width=1200)');

        $this->assertSame('![Shot](posts-assets/a/shot.png){width=1200 height=630}', $result->content);
        $this->assertSame([[
            'from' => self::CDN . 'posts-assets/a/shot.png?width=1200',
            'to' => 'posts-assets/a/shot.png',
            'attributes' => '{width=1200 height=630}',
        ]], $result->replacements);
    }

    public function testExistingAttributesAreMergedBeforeDimensions(): void
    {
        $rewriter = new ImageReferenceRewriter($this->probeWithPng());

        $result = $rewriter->rewrite('![x](' . self::CDN . 'posts-assets/a/b.png){fetchpriority=high}');

        $this->assertSame('![x](posts-assets/a/b.png){fetchpriority=high width=1200 height=630}', $result->content);
    }

    public function testTitleIsKept(): void
    {
        $rewriter = new ImageReferenceRewriter($this->probeWithPng());

        $result = $rewriter->rewrite('![x](' . self::CDN . 'posts-assets/a/b.png "Title")');

        $this->assertSame('![x](posts-assets/a/b.png "Title"){width=1200 height=630}', $result->content);
    }

    public function testDirectMinioUrlBecomesKey(): void
    {
        $rewriter = new ImageReferenceRewriter($this->probeWithPng());

        $result = $rewriter->rewrite('![x](https://minio.kocal.fr/hugo.alliau.me/posts-assets/a/b.png)');

        $this->assertSame('![x](posts-assets/a/b.png){width=1200 height=630}', $result->content);
    }

    public function testExternalUrlIsUntouched(): void
    {
        $rewriter = new ImageReferenceRewriter(new InMemoryImageProbe());

        $result = $rewriter->rewrite('![x](https://example.com/i.png)');

        $this->assertSame('![x](https://example.com/i.png)', $result->content);
        $this->assertSame([], $result->replacements);
    }

    public function testMissingObjectKeepsTheUrlAndReportsTheKey(): void
    {
        $rewriter = new ImageReferenceRewriter(new InMemoryImageProbe());
        $markdown = '![x](' . self::CDN . 'posts-assets/a/b.png)';

        $result = $rewriter->rewrite($markdown);

        $this->assertSame($markdown, $result->content);
        $this->assertSame(['posts-assets/a/b.png'], $result->missingKeys);
    }

    public function testUnreadableDimensionsKeepTheUrlAndReportTheKey(): void
    {
        $rewriter = new ImageReferenceRewriter(new InMemoryImageProbe([
            'posts-assets/a/b.svg' => false,
        ]));
        $markdown = '![x](' . self::CDN . 'posts-assets/a/b.svg)';

        $result = $rewriter->rewrite($markdown);

        $this->assertSame($markdown, $result->content);
        $this->assertSame(['posts-assets/a/b.svg'], $result->unreadableKeys);
        $this->assertSame([], $result->missingKeys);
    }

    public function testExistingDimensionsAreNotProbed(): void
    {
        $probe = new InMemoryImageProbe();
        $rewriter = new ImageReferenceRewriter($probe);

        $result = $rewriter->rewrite('![x](' . self::CDN . 'posts-assets/a/b.png){width=10 height=5}');

        $this->assertSame('![x](posts-assets/a/b.png){width=10 height=5}', $result->content);
        $this->assertSame([], $probe->probed);
    }

    public function testWidthOnlyBlockGetsAComputedHeight(): void
    {
        $rewriter = new ImageReferenceRewriter($this->probeWithPng());

        $result = $rewriter->rewrite('![x](' . self::CDN . 'posts-assets/a/b.png){width=600}');

        $this->assertSame('![x](posts-assets/a/b.png){width=600 height=315}', $result->content);
    }

    public function testHeightOnlyBlockGetsAComputedWidth(): void
    {
        $rewriter = new ImageReferenceRewriter($this->probeWithPng());

        $result = $rewriter->rewrite('![x](' . self::CDN . 'posts-assets/a/b.png){height=315}');

        $this->assertSame('![x](posts-assets/a/b.png){height=315 width=600}', $result->content);
    }

    public function testDataWidthDoesNotCountAsADimension(): void
    {
        $rewriter = new ImageReferenceRewriter($this->probeWithPng());

        $result = $rewriter->rewrite('![x](' . self::CDN . 'posts-assets/a/b.png){data-width=600}');

        $this->assertSame('![x](posts-assets/a/b.png){data-width=600 width=1200 height=630}', $result->content);
    }

    public function testEmptyAttributeBlockIsReplacedWithoutStraySpace(): void
    {
        $rewriter = new ImageReferenceRewriter($this->probeWithPng());

        $result = $rewriter->rewrite('![x](' . self::CDN . 'posts-assets/a/b.png){}');

        $this->assertSame('![x](posts-assets/a/b.png){width=1200 height=630}', $result->content);
    }

    public function testRewriteIsIdempotent(): void
    {
        $rewriter = new ImageReferenceRewriter($this->probeWithPng());
        $markdown = '![x](' . self::CDN . 'posts-assets/a/b.png?width=800)';

        $once = $rewriter->rewrite($markdown)->content;
        $twice = $rewriter->rewrite($once)->content;

        $this->assertSame($once, $twice);
    }

    private function probeWithPng(): InMemoryImageProbe
    {
        return new InMemoryImageProbe([
            'posts-assets/a/b.png' => new ImageDimensions(1200, 630),
        ]);
    }
}
