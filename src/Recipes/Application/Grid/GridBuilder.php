<?php

declare(strict_types=1);

namespace App\Recipes\Application\Grid;

use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Domain\Data\Step;

/**
 * Transforme l'arbre d'une recette en grille de cellules avec rowspan et colspan.
 *
 * La colonne d'une étape se calcule depuis les feuilles (1 + le max de ses enfants) et non
 * depuis la racine: une préparation courte reste ainsi collée à gauche même quand sa sortie
 * n'est consommée que très à droite.
 */
final readonly class GridBuilder
{
    public function __invoke(RecipeContent $content): Grid
    {
        /** @var array<string, array{col: int, rowStart: int, rowSpan: int}> $metrics */
        $metrics = [];
        $rowCount = 0;

        foreach ($content->roots as $root) {
            $this->measure($root, $metrics, $rowCount);
        }

        $columnCount = 1;
        foreach ($metrics as $metric) {
            $columnCount = max($columnCount, $metric['col'] + 1);
        }

        /** @var array<int, list<Cell>> $cellsByRow */
        $cellsByRow = array_fill(0, max($rowCount, 1), []);

        foreach ($content->roots as $root) {
            $this->place($root, $columnCount, $metrics, $cellsByRow);
        }

        $rows = [];
        for ($rowIndex = 0; $rowIndex < $rowCount; ++$rowIndex) {
            $cells = $cellsByRow[$rowIndex];
            usort($cells, static fn (Cell $a, Cell $b): int => $a->col <=> $b->col);
            $rows[] = new Row($cells);
        }

        return new Grid($rows, $columnCount);
    }

    /**
     * @param array<string, array{col: int, rowStart: int, rowSpan: int}> $metrics
     *
     * @return array{col: int, rowStart: int, rowSpan: int}
     */
    private function measure(Step|Ingredient $node, array &$metrics, int &$rowCount): array
    {
        $rowStart = $rowCount;

        if ($node instanceof Ingredient) {
            ++$rowCount;

            return $metrics[$node->id] = [
                'col' => 0,
                'rowStart' => $rowStart,
                'rowSpan' => 1,
            ];
        }

        $col = 0;
        foreach ($node->children as $child) {
            $childMetrics = $this->measure($child, $metrics, $rowCount);
            $col = max($col, $childMetrics['col'] + 1);
        }

        return $metrics[$node->id] = [
            'col' => $col,
            'rowStart' => $rowStart,
            'rowSpan' => $rowCount - $rowStart,
        ];
    }

    /**
     * @param array<string, array{col: int, rowStart: int, rowSpan: int}> $metrics
     * @param array<int, list<Cell>>                                      $cellsByRow
     */
    private function place(Step|Ingredient $node, int $parentCol, array $metrics, array &$cellsByRow): void
    {
        $metric = $metrics[$node->id];

        $cellsByRow[$metric['rowStart']][] = new Cell(
            $node instanceof Ingredient ? CellType::INGREDIENT : CellType::STEP,
            $metric['col'],
            $metric['rowSpan'],
            1,
            $node,
        );

        $gap = $parentCol - $metric['col'] - 1;
        if ($gap > 0) {
            $cellsByRow[$metric['rowStart']][] = new Cell(
                CellType::GAP,
                $metric['col'] + 1,
                $metric['rowSpan'],
                $gap,
            );
        }

        if ($node instanceof Step) {
            foreach ($node->children as $child) {
                $this->place($child, $metric['col'], $metrics, $cellsByRow);
            }
        }
    }
}
