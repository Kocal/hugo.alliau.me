<?php

declare(strict_types=1);

namespace App\Tests\Blog\Infrastructure\Validator;

use App\Blog\Infrastructure\Validator\RenderableMarkdown;
use App\Blog\Infrastructure\Validator\RenderableMarkdownValidator;
use App\Shared\Infrastructure\Markdown\Extension\Image\Exception\MissingImageDimensionsException;
use App\Tests\Blog\Infrastructure\Double\Markdown\ThrowingMarkdownConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<RenderableMarkdownValidator>
 */
#[CoversClass(RenderableMarkdownValidator::class)]
#[CoversClass(RenderableMarkdown::class)]
final class RenderableMarkdownValidatorTest extends ConstraintValidatorTestCase
{
    private ThrowingMarkdownConverter $converter;

    public function testMissingDimensionsAddAViolationWithTheExceptionMessage(): void
    {
        $exception = MissingImageDimensionsException::forUrl('posts-assets/a/b.png');
        $this->converter = new ThrowingMarkdownConverter($exception);

        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);
        $this->validator->validate('![x](posts-assets/a/b.png)', new RenderableMarkdown());

        $this->buildViolation($exception->getMessage())->assertRaised();
    }

    public function testRenderableContentHasNoViolation(): void
    {
        $this->converter = new ThrowingMarkdownConverter();

        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);
        $this->validator->validate('# Hello', new RenderableMarkdown());

        $this->assertNoViolation();
        $this->assertSame(1, $this->converter->calls);
    }

    public function testNullAndEmptyAreIgnoredWithoutConverting(): void
    {
        $this->converter = new ThrowingMarkdownConverter(new \LogicException('must not be called'));

        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);
        $this->validator->validate(null, new RenderableMarkdown());
        $this->validator->validate('', new RenderableMarkdown());

        $this->assertNoViolation();
        $this->assertSame(0, $this->converter->calls);
    }

    protected function createValidator(): RenderableMarkdownValidator
    {
        $this->converter ??= new ThrowingMarkdownConverter();

        return new RenderableMarkdownValidator($this->converter);
    }
}
