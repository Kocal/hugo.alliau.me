<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Infrastructure\Doctrine\DBAL\Type;

use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Domain\Data\Step;
use App\Recipes\Domain\Data\Unit;
use App\Recipes\Infrastructure\Doctrine\DBAL\Type\RecipeContentType;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecipeContentType::class)]
#[UsesClass(RecipeContent::class)]
#[UsesClass(Step::class)]
#[UsesClass(Ingredient::class)]
final class RecipeContentTypeTest extends TestCase
{
    private RecipeContentType $type;

    private PostgreSQLPlatform $platform;

    #[\Override]
    protected function setUp(): void
    {
        $this->type = new RecipeContentType();
        $this->platform = new PostgreSQLPlatform();
    }

    public function testItDeclaresJsonb(): void
    {
        $this->assertSame('JSONB', $this->type->getSQLDeclaration([], $this->platform));
    }

    public function testItRoundTrips(): void
    {
        $content = new RecipeContent([
            new Step('s1', 'Mélanger farine de riz et eau', [
                new Ingredient('i1', 'de farine de riz gluant', 2.0, null, Unit::TBSP),
                new Ingredient('i2', "d'eau", 1.0, 2.0, Unit::TBSP),
            ]),
            new Ingredient('i3', 'concombre coupé en julienne', 0.5),
        ]);

        $json = $this->type->convertToDatabaseValue($content, $this->platform);
        $this->assertIsString($json);

        $restored = $this->type->convertToPHPValue($json, $this->platform);

        $this->assertEquals($content, $restored);
    }

    public function testItKeepsTheDiscriminatorInTheSerializedShape(): void
    {
        $content = new RecipeContent([new Ingredient('i1', 'de sel')]);

        $json = $this->type->convertToDatabaseValue($content, $this->platform);
        $decoded = json_decode((string) $json, true, 512, \JSON_THROW_ON_ERROR);

        $this->assertSame('ingredient', $decoded['roots'][0]['type']);
    }

    public function testNullRoundTripsToNull(): void
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
        $this->assertNotInstanceOf(\App\Recipes\Domain\Data\RecipeContent::class, $this->type->convertToPHPValue(null, $this->platform));
    }

    public function testItRejectsMalformedJson(): void
    {
        $this->expectException(ValueNotConvertible::class);

        $this->type->convertToPHPValue('{"roots": [{"type": "step"}]}', $this->platform);
    }

    public function testItRejectsAStepWithoutChildren(): void
    {
        $this->expectException(ValueNotConvertible::class);

        $this->type->convertToPHPValue(
            '{"roots": [{"type": "step", "id": "s1", "text": "Mélanger", "children": []}]}',
            $this->platform,
        );
    }

    public function testItRejectsSyntacticallyInvalidJson(): void
    {
        $this->expectException(ValueNotConvertible::class);

        $this->type->convertToPHPValue('{not valid json at all}}}', $this->platform);
    }

    public function testItRejectsAStepNodeShapedLikeAnIngredient(): void
    {
        $this->expectException(ValueNotConvertible::class);

        $this->type->convertToPHPValue(
            '{"roots": [{"type": "step", "id": "s1", "label": "not a real step"}]}',
            $this->platform,
        );
    }

    public function testItRejectsANodeMixingStepAndIngredientFields(): void
    {
        $this->expectException(ValueNotConvertible::class);

        $this->type->convertToPHPValue(
            '{"roots": [{"type": "step", "id": "s1", "text": "Mélanger", "children": [{"type": "ingredient", "id": "i1", "label": "sel"}], "label": "oops"}]}',
            $this->platform,
        );
    }

    public function testItRejectsAnUnknownNodeType(): void
    {
        $this->expectException(ValueNotConvertible::class);

        $this->type->convertToPHPValue(
            '{"roots": [{"type": "garnish", "id": "i1"}]}',
            $this->platform,
        );
    }

    public function testItRejectsTwoIngredientsSharingAnIdInDifferentBranches(): void
    {
        $this->expectException(ValueNotConvertible::class);

        $this->type->convertToPHPValue(
            '{"roots": ['
            . '{"type": "step", "id": "s1", "text": "Mélanger", "children": [{"type": "ingredient", "id": "dup", "label": "sel"}]},'
            . '{"type": "ingredient", "id": "dup", "label": "poivre"}'
            . ']}',
            $this->platform,
        );
    }

    public function testItRejectsAStepAndAnIngredientSharingAnId(): void
    {
        $this->expectException(ValueNotConvertible::class);

        $this->type->convertToPHPValue(
            '{"roots": ['
            . '{"type": "step", "id": "x1", "text": "Mélanger", "children": [{"type": "ingredient", "id": "i1", "label": "sel"}]},'
            . '{"type": "ingredient", "id": "x1", "label": "poivre"}'
            . ']}',
            $this->platform,
        );
    }

    public function testItRejectsANodeWithAnEmptyId(): void
    {
        $this->expectException(ValueNotConvertible::class);

        $this->type->convertToPHPValue(
            '{"roots": [{"type": "ingredient", "id": "", "label": "sel"}]}',
            $this->platform,
        );
    }
}
