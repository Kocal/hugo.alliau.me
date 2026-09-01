<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\EasyAdmin\Form;

use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Infrastructure\Doctrine\DBAL\Type\RecipeContentType as RecipeContentDbalType;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<RecipeContent, string>
 */
final readonly class RecipeContentTransformer implements DataTransformerInterface
{
    private PostgreSQLPlatform $platform;

    public function __construct(
        private RecipeContentDbalType $type = new RecipeContentDbalType(),
    ) {
        $this->platform = new PostgreSQLPlatform();
    }

    #[\Override]
    public function transform(mixed $value): string
    {
        return $this->type->convertToDatabaseValue($value ?? new RecipeContent(), $this->platform) ?? '';
    }

    #[\Override]
    public function reverseTransform(mixed $value): RecipeContent
    {
        if ($value === null || $value === '') {
            return new RecipeContent();
        }

        try {
            return $this->type->convertToPHPValue($value, $this->platform) ?? new RecipeContent();
        } catch (ConversionException $conversionException) {
            throw new TransformationFailedException('Le contenu de la recette est invalide.', $conversionException->getCode(), previous: $conversionException);
        }
    }
}
