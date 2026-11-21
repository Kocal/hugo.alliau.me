<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Validator;

use App\Shared\Domain\Markdown\MarkdownConverter;
use App\Shared\Infrastructure\Markdown\Extension\Image\Exception\MissingImageDimensionsException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class RenderableMarkdownValidator extends ConstraintValidator
{
    public function __construct(
        private readonly MarkdownConverter $markdownConverter,
    ) {
    }

    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof RenderableMarkdown) {
            throw new UnexpectedTypeException($constraint, RenderableMarkdown::class);
        }

        if (! \is_string($value) || $value === '') {
            return;
        }

        try {
            ($this->markdownConverter)($value);
        } catch (MissingImageDimensionsException $missingImageDimensionsException) {
            $this->context->buildViolation($missingImageDimensionsException->getMessage())->addViolation();
        }
    }
}
