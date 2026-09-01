<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Infrastructure\Twig\Components;

use App\Recipes\Application\Grid\Cell;
use App\Recipes\Application\Grid\Grid as GridModel;
use App\Recipes\Application\Grid\GridBuilder;
use App\Recipes\Application\Grid\Row;
use App\Recipes\Application\QuantityFormatter;
use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Domain\Data\Step;
use App\Recipes\Domain\Data\Unit;
use App\Recipes\Infrastructure\Twig\Components\Grid;
use App\Shared\Application\Twig\Extension\I18nExtension;
use App\Shared\Domain\Data\UuidTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Translation\LocaleSwitcher;
use Twig\Environment;

#[CoversClass(Grid::class)]
#[UsesClass(GridBuilder::class)]
#[UsesClass(GridModel::class)]
#[UsesClass(Row::class)]
#[UsesClass(Cell::class)]
#[UsesClass(QuantityFormatter::class)]
#[UsesClass(Recipe::class)]
#[UsesClass(RecipeContent::class)]
#[UsesClass(Step::class)]
#[UsesClass(Ingredient::class)]
#[UsesClass(Unit::class)]
#[UsesClass(I18nExtension::class)]
#[UsesTrait(UuidTrait::class)]
final class GridTest extends KernelTestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testItRendersRowSpansAndQuantities(): void
    {
        self::bootKernel();

        $recipe = new Recipe()
            ->setName('Nouilles udon')
            ->setSlug('nouilles-udon')
            ->setServings(4)
            ->setContent(new RecipeContent([
                new Step('s1', 'Mélanger farine et eau', [
                    new Ingredient('i1', 'de farine de riz gluant', 2.0, null, Unit::TBSP),
                    new Ingredient('i2', 'concombre en julienne', 0.5),
                ]),
            ]));

        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');
        /** @var LocaleSwitcher $localeSwitcher */
        $localeSwitcher = self::getContainer()->get(LocaleSwitcher::class);
        $html = $localeSwitcher->runWithLocale(
            'fr',
            static fn (): string => $twig->createTemplate('<twig:Recipe:Grid :recipe="recipe" />')
                ->render([
                    'recipe' => $recipe,
                ]),
        );

        $crawler = new Crawler($html);

        $caption = $crawler->filter('[data-testid="recipe-grid-caption"]');
        $this->assertSame('Ingrédients et étapes pour Nouilles udon', $caption->text());

        $stepButton = $crawler->filter('[data-node-id="s1"]');
        $this->assertSame('button', $stepButton->nodeName());
        $this->assertSame('false', $stepButton->attr('aria-pressed'));
        $this->assertNull($stepButton->attr('role'));
        $this->assertNull($stepButton->attr('tabindex'));

        $stepCell = $stepButton->closest('td');
        $this->assertInstanceOf(Crawler::class, $stepCell);
        $this->assertSame('2', $stepCell->attr('rowspan'));
        $this->assertNull($stepCell->attr('data-node-id'));
        $this->assertNull($stepCell->attr('role'));

        $ingredientOneButton = $crawler->filter('[data-node-id="i1"]');
        $this->assertSame('button', $ingredientOneButton->nodeName());
        $this->assertStringContainsString('cuillères à soupe', $ingredientOneButton->text());
        $this->assertSame('false', $ingredientOneButton->attr('aria-pressed'));
        $this->assertNull($ingredientOneButton->attr('role'));
        $this->assertNull($ingredientOneButton->attr('tabindex'));

        $ingredientOneCell = $ingredientOneButton->closest('td');
        $this->assertInstanceOf(Crawler::class, $ingredientOneCell);
        $this->assertSame('1', $ingredientOneCell->attr('rowspan'));
        $this->assertSame('2', $ingredientOneCell->attr('data-qty-min'));
        $this->assertNull($ingredientOneCell->attr('data-node-id'));

        $ingredientTwoButton = $crawler->filter('[data-node-id="i2"]');
        $this->assertStringContainsString('½', $ingredientTwoButton->text());
        $this->assertSame('false', $ingredientTwoButton->attr('aria-pressed'));
        $this->assertNull($ingredientTwoButton->attr('role'));
        $this->assertNull($ingredientTwoButton->attr('tabindex'));

        // role="button" no longer exists anywhere: cells keep their implicit table-cell role.
        $this->assertCount(0, $crawler->filter('[role="button"]'));
        $this->assertCount(0, $crawler->filter('td[tabindex]'));
    }

    public function testGapCellsStayCompletelyInert(): void
    {
        self::bootKernel();

        // A step whose own row-span is shorter than its sibling's leaves a gap cell behind.
        $recipe = new Recipe()
            ->setName('Nouilles udon')
            ->setSlug('nouilles-udon')
            ->setServings(4)
            ->setContent(new RecipeContent([
                new Step('outer', 'Servir', [
                    new Step('inner', 'Mélanger farine et eau', [
                        new Ingredient('i1', 'de farine de riz gluant', 2.0, null, Unit::TBSP),
                    ]),
                    new Ingredient('i2', 'concombre en julienne', 0.5),
                ]),
            ]));

        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');
        $html = $twig->createTemplate('<twig:Recipe:Grid :recipe="recipe" />')
            ->render([
                'recipe' => $recipe,
            ]);

        $crawler = new Crawler($html);

        $gapCells = $crawler->filter('[data-testid="recipe-grid-gap"]');
        $this->assertGreaterThan(0, $gapCells->count());

        foreach ($gapCells as $gapCell) {
            $gapCrawler = new Crawler($gapCell);
            $this->assertNull($gapCrawler->attr('role'));
            $this->assertNull($gapCrawler->attr('tabindex'));
            $this->assertNull($gapCrawler->attr('data-node-id'));
            $this->assertCount(0, $gapCrawler->filter('button'));
        }
    }

    public function testItRendersTheDecimalSeparatorForTheCurrentLocale(): void
    {
        self::bootKernel();

        $recipe = new Recipe()
            ->setSlug('nouilles-udon')
            ->setServings(4)
            ->setContent(new RecipeContent([
                new Step('s1', 'Mélanger farine et eau', [
                    new Ingredient('i1', 'de sucre', 1.4),
                ]),
            ]));

        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');
        /** @var LocaleSwitcher $localeSwitcher */
        $localeSwitcher = self::getContainer()->get(LocaleSwitcher::class);
        $html = $localeSwitcher->runWithLocale(
            'en',
            static fn (): string => $twig->createTemplate('<twig:Recipe:Grid :recipe="recipe" />')
                ->render([
                    'recipe' => $recipe,
                ]),
        );

        $crawler = new Crawler($html);

        $this->assertStringContainsString('1.4', $crawler->filter('[data-node-id="i1"]')->text());
    }

    public function testItRendersEachStepWithItsInputsForSmallScreens(): void
    {
        self::bootKernel();

        $recipe = new Recipe()
            ->setSlug('nouilles-udon')
            ->setContent(new RecipeContent([
                new Step('outer', 'Servir', [
                    new Step('inner', 'Mélanger farine et eau', [
                        new Ingredient('i1', 'de farine de riz gluant', 2.0, null, Unit::TBSP),
                    ]),
                    new Ingredient('i2', 'concombre en julienne', 0.5),
                ]),
            ]));

        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');
        $html = $twig->createTemplate('<twig:Recipe:Grid :recipe="recipe" />')
            ->render([
                'recipe' => $recipe,
            ]);

        $crawler = new Crawler($html);
        $mobileSteps = $crawler->filter('[data-testid="recipe-step"]');
        $this->assertCount(2, $mobileSteps);

        // Ordre postfixe: l'étape interne est listée avant celle qui la consomme.
        $this->assertSame(
            ['inner', 'outer'],
            $mobileSteps->filter('[data-testid="recipe-step-text"]')
                ->extract(['data-node-id']),
        );

        // Sans ses entrées, « Mélanger farine et eau » ne dirait pas quoi mélanger.
        $this->assertStringContainsString('de farine de riz gluant', $mobileSteps->eq(0)->text());

        // Une étape qui consomme le résultat d'une autre la désigne par son numéro.
        $this->assertStringContainsString('the result of step 1', $mobileSteps->eq(1)->text());
    }
}
