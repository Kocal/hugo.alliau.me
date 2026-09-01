<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Application\Grid;

use App\Recipes\Application\Grid\Cell;
use App\Recipes\Application\Grid\Grid;
use App\Recipes\Application\Grid\GridBuilder;
use App\Recipes\Application\Grid\Row;
use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Domain\Data\Step;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GridBuilder::class)]
#[UsesClass(Grid::class)]
#[UsesClass(Row::class)]
#[UsesClass(Cell::class)]
#[UsesClass(RecipeContent::class)]
#[UsesClass(Step::class)]
#[UsesClass(Ingredient::class)]
final class GridBuilderTest extends TestCase
{
    public function testEmptyContentProducesAnEmptyGrid(): void
    {
        $grid = (new GridBuilder())(new RecipeContent());

        $this->assertSame([], $grid->rows);
        $this->assertSame(1, $grid->columnCount);
    }

    public function testASingleIngredientProducesOneRowAndOneColumn(): void
    {
        $grid = (new GridBuilder())(new RecipeContent([new Ingredient('i1', 'de sel')]));

        $this->assertCount(1, $grid->rows);
        $this->assertSame(1, $grid->columnCount);
        $this->assertCount(1, $grid->rows[0]->cells);
        $this->assertTrue($grid->rows[0]->cells[0]->isIngredient());
    }

    public function testASingleStepSpansItsIngredients(): void
    {
        $content = new RecipeContent([
            new Step('s1', 'Mélanger', [
                new Ingredient('i1', 'de farine'),
                new Ingredient('i2', "d'eau"),
            ]),
        ]);

        $grid = (new GridBuilder())($content);

        $this->assertSame(2, $grid->columnCount);
        $this->assertCount(2, $grid->rows);
        $this->assertSame([0, 1], array_map(static fn (Cell $c): int => $c->col, $grid->rows[0]->cells));
        $this->assertSame(2, $grid->rows[0]->cells[1]->rowSpan);
        $this->assertCount(1, $grid->rows[1]->cells);
    }

    public function testTheUdonRecipeMatchesTheReferenceLayout(): void
    {
        $grid = (new GridBuilder())($this->udon());

        $this->assertSame(7, $grid->columnCount);
        $this->assertCount(13, $grid->rows);

        // Chaque ligne totalise 7 colonnes une fois les rowSpan des lignes du dessus pris en compte.
        $occupied = array_fill(0, 13, 0);
        foreach ($grid->rows as $rowIndex => $row) {
            foreach ($row->cells as $cell) {
                for ($r = $rowIndex; $r < $rowIndex + $cell->rowSpan; ++$r) {
                    $occupied[$r] += $cell->colSpan;
                }
            }
        }

        $this->assertSame(array_fill(0, 13, 7), $occupied);
    }

    public function testTheUdonRecipePlacesEachStepInTheExpectedColumn(): void
    {
        $grid = (new GridBuilder())($this->udon());

        $expected = [
            's_beans' => [
                'col' => 1,
                'rowStart' => 0,
                'rowSpan' => 1,
            ],
            's_pork' => [
                'col' => 1,
                'rowStart' => 1,
                'rowSpan' => 3,
            ],
            's_veggies' => [
                'col' => 2,
                'rowStart' => 1,
                'rowSpan' => 5,
            ],
            's_paste' => [
                'col' => 3,
                'rowStart' => 0,
                'rowSpan' => 8,
            ],
            's_water' => [
                'col' => 4,
                'rowStart' => 0,
                'rowSpan' => 9,
            ],
            's_flour' => [
                'col' => 1,
                'rowStart' => 9,
                'rowSpan' => 2,
            ],
            's_thicken' => [
                'col' => 5,
                'rowStart' => 0,
                'rowSpan' => 11,
            ],
            's_noodles' => [
                'col' => 1,
                'rowStart' => 11,
                'rowSpan' => 1,
            ],
            's_serve' => [
                'col' => 6,
                'rowStart' => 0,
                'rowSpan' => 13,
            ],
        ];

        $actual = [];
        foreach ($grid->rows as $rowIndex => $row) {
            foreach ($row->cells as $cell) {
                if ($cell->isStep()) {
                    $actual[$cell->node->id] = [
                        'col' => $cell->col,
                        'rowStart' => $rowIndex,
                        'rowSpan' => $cell->rowSpan,
                    ];
                }
            }
        }

        // Les étapes sont découvertes dans l'ordre des lignes, pas dans celui de la recette:
        // trier les deux tableaux pour que la comparaison porte sur les valeurs et non sur l'ordre des clés.
        ksort($expected);
        ksort($actual);

        $this->assertSame($expected, $actual);
    }

    public function testTheUdonRecipeOrdersIngredientsByDepthFirstTraversal(): void
    {
        $grid = (new GridBuilder())($this->udon());

        $labels = [];
        foreach ($grid->rows as $row) {
            foreach ($row->cells as $cell) {
                if ($cell->isIngredient()) {
                    $labels[] = $cell->node->id;
                }
            }
        }

        $this->assertSame([
            'i_beans', 'i_pork', 'i_garlic', 'i_onion', 'i_zucchini', 'i_cabbage',
            'i_sugar', 'i_gochugaru', 'i_boiling_water', 'i_rice_flour', 'i_flour_water',
            'i_udon', 'i_cucumber',
        ], $labels);
    }

    public function testGapsFillTheDistanceToTheParentColumn(): void
    {
        $grid = (new GridBuilder())($this->udon());

        $gaps = [];
        foreach ($grid->rows as $rowIndex => $row) {
            foreach ($row->cells as $cell) {
                if ($cell->isGap()) {
                    $gaps[] = [$rowIndex, $cell->col, $cell->colSpan, $cell->rowSpan];
                }
            }
        }

        $this->assertContains([0, 2, 1, 1], $gaps, 'le trou à droite de "faire revenir la pâte"');
        $this->assertContains([9, 2, 3, 2], $gaps, 'le trou à droite de "mélanger farine de riz et eau"');
        $this->assertContains([11, 2, 4, 1], $gaps, 'le trou à droite de "cuire les nouilles"');
        $this->assertContains([12, 1, 5, 1], $gaps, 'le trou à droite du concombre');
    }

    private function udon(): RecipeContent
    {
        return new RecipeContent([
            new Step('s_serve', 'Servir nouilles, sauce et concombre', [
                new Step('s_thicken', 'Verser mélange farine, laisser épaissir', [
                    new Step('s_water', 'Verser eau bouillante, mijoter jusqu\'à cuisson', [
                        new Step('s_paste', 'Ajouter pâte réservée, sucre et gochugaru', [
                            new Step('s_beans', 'Faire revenir la pâte de haricots noirs, 15 min', [
                                new Ingredient('i_beans', 'de pâte de haricots noirs'),
                            ]),
                            new Step('s_veggies', 'Ajouter courgette et chou, cuire 5 min', [
                                new Step('s_pork', 'Cuire le porc, ajouter ail et oignons, 5 min', [
                                    new Ingredient('i_pork', 'de poitrine de porc coupée en petits dés'),
                                    new Ingredient('i_garlic', "d'ail émincées"),
                                    new Ingredient('i_onion', 'oignons coupés en petits tronçons'),
                                ]),
                                new Ingredient('i_zucchini', 'courgette coupée en petits dés'),
                                new Ingredient('i_cabbage', 'chou coupé en petits tronçons'),
                            ]),
                            new Ingredient('i_sugar', 'de sucre'),
                            new Ingredient('i_gochugaru', 'de gochugaru'),
                        ]),
                        new Ingredient('i_boiling_water', "d'eau bouillante"),
                    ]),
                    new Step('s_flour', 'Mélanger farine de riz et eau', [
                        new Ingredient('i_rice_flour', 'de farine de riz gluant'),
                        new Ingredient('i_flour_water', "d'eau"),
                    ]),
                ]),
                new Step('s_noodles', 'Cuire nouilles Udon, égoutter', [
                    new Ingredient('i_udon', 'de nouilles Udon'),
                ]),
                new Ingredient('i_cucumber', 'concombre coupé en julienne'),
            ]),
        ]);
    }
}
