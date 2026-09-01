<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Infrastructure\EasyAdmin\Form;

use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Domain\Data\Step;
use App\Recipes\Infrastructure\Doctrine\DBAL\Type\RecipeContentType as RecipeContentDbalType;
use App\Recipes\Infrastructure\EasyAdmin\Form\RecipeContentTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

#[CoversClass(RecipeContentTransformer::class)]
#[UsesClass(RecipeContentDbalType::class)]
#[UsesClass(RecipeContent::class)]
#[UsesClass(Step::class)]
#[UsesClass(Ingredient::class)]
final class RecipeContentTransformerTest extends TestCase
{
    public function testItRoundTrips(): void
    {
        $transformer = new RecipeContentTransformer(new RecipeContentDbalType());
        $content = new RecipeContent([
            new Step('s1', 'Mélanger', [new Ingredient('i1', 'de farine', 200.0)]),
        ]);

        $json = $transformer->transform($content);
        $this->assertIsString($json);
        $this->assertEquals($content, $transformer->reverseTransform($json));
    }

    public function testAnEmptyValueBecomesAnEmptyContent(): void
    {
        $transformer = new RecipeContentTransformer(new RecipeContentDbalType());

        $this->assertEquals(new RecipeContent(), $transformer->reverseTransform(''));
        $this->assertEquals(new RecipeContent(), $transformer->reverseTransform(null));
    }

    public function testAStructurallyInvalidNodeBecomesAFormError(): void
    {
        $transformer = new RecipeContentTransformer(new RecipeContentDbalType());

        $this->expectException(TransformationFailedException::class);

        $transformer->reverseTransform('{"roots": [{"type": "step"}]}');
    }

    public function testSyntacticallyInvalidJsonBecomesAFormError(): void
    {
        $transformer = new RecipeContentTransformer(new RecipeContentDbalType());

        $this->expectException(TransformationFailedException::class);

        $transformer->reverseTransform('{not even json');
    }
}
