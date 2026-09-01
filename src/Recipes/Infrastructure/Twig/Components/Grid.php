<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\Twig\Components;

use App\Recipes\Application\Grid\Grid as GridModel;
use App\Recipes\Application\Grid\GridBuilder;
use App\Recipes\Application\QuantityFormatter;
use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\Step;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Grid
{
    public Recipe $recipe;

    private ?GridModel $grid = null;

    private ?string $decimalSeparator = null;

    /**
     * @var array<string, int>|null
     */
    private ?array $numbering = null;

    public function __construct(
        private readonly GridBuilder $gridBuilder,
        private readonly QuantityFormatter $quantityFormatter,
        private readonly LocaleSwitcher $localeSwitcher,
    ) {
    }

    public function getGrid(): GridModel
    {
        return $this->grid ??= ($this->gridBuilder)($this->recipe->getContent());
    }

    public function formatQuantity(?float $value): ?string
    {
        return $value === null ? null : $this->quantityFormatter->format($value, $this->getDecimalSeparator());
    }

    /**
     * Le même caractère que celui que le navigateur choisira via Intl.NumberFormat pour la
     * même locale, les deux s'appuyant sur les données CLDR.
     */
    private function getDecimalSeparator(): string
    {
        if ($this->decimalSeparator === null) {
            $symbol = new \NumberFormatter($this->localeSwitcher->getLocale(), \NumberFormatter::DECIMAL)
                ->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

            $this->decimalSeparator = $symbol !== false ? $symbol : ',';
        }

        return $this->decimalSeparator;
    }

    /**
     * Renvoie l'unité sous forme de TranslatableInterface portant le compte voulu, à passer
     * au filtre `trans` dans le template.
     *
     * Le compte doit être fixé ICI et pas dans le template: le filtre `trans` appliqué à un
     * TranslatableInterface ignore ses arguments et n'utilise que les paramètres déjà portés
     * par le message. `{{ message|trans({ count: 3 }) }}` rendrait donc toujours le pluriel
     * figé à la construction.
     */
    public function unitFor(Ingredient $ingredient, float $count): ?TranslatableInterface
    {
        return $ingredient->unit?->toTranslatable($count);
    }

    /**
     * @return list<Ingredient>
     */
    public function getFlatIngredients(): array
    {
        $ingredients = [];
        foreach ($this->getGrid()->rows as $row) {
            foreach ($row->cells as $cell) {
                if ($cell->node instanceof Ingredient) {
                    $ingredients[] = $cell->node;
                }
            }
        }

        return $ingredients;
    }

    /**
     * Ordre postfixe: une étape apparaît après celles dont elle consomme la sortie,
     * ce qui en fait un ordre d'exécution valide.
     *
     * @return list<Step>
     */
    public function getFlatSteps(): array
    {
        $steps = [];
        foreach ($this->recipe->getContent()->roots as $root) {
            $this->collectSteps($root, $steps);
        }

        return $steps;
    }

    /**
     * Les ingrédients qui entrent directement dans cette étape.
     *
     * @return list<Ingredient>
     */
    public function directIngredients(Step $step): array
    {
        return array_values(array_filter(
            $step->children,
            static fn (Step|Ingredient $child): bool => $child instanceof Ingredient,
        ));
    }

    /**
     * Les numéros des étapes dont celle-ci consomme le résultat. L'ordre postfixe de
     * getFlatSteps() garantit qu'ils sont tous inférieurs au numéro de cette étape.
     *
     * @return list<int>
     */
    public function requiredStepNumbers(Step $step): array
    {
        $numbering = $this->getNumbering();

        $required = [];
        foreach ($step->children as $child) {
            if ($child instanceof Step) {
                $required[] = $numbering[$child->id];
            }
        }

        return $required;
    }

    /**
     * @return array<string, int>
     */
    private function getNumbering(): array
    {
        if ($this->numbering === null) {
            $this->numbering = [];
            foreach ($this->getFlatSteps() as $index => $step) {
                $this->numbering[$step->id] = $index + 1;
            }
        }

        return $this->numbering;
    }

    /**
     * @param list<Step> $steps
     */
    private function collectSteps(Step|Ingredient $node, array &$steps): void
    {
        if (! $node instanceof Step) {
            return;
        }

        foreach ($node->children as $child) {
            $this->collectSteps($child, $steps);
        }

        $steps[] = $node;
    }
}
