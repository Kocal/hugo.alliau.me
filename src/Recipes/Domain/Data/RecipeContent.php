<?php

declare(strict_types=1);

namespace App\Recipes\Domain\Data;

final readonly class RecipeContent
{
    /**
     * @param list<Step|Ingredient> $roots
     */
    public function __construct(
        public array $roots = [],
    ) {
        $seenIds = [];

        foreach ($this->roots as $root) {
            $this->assertNode($root, $seenIds);
        }
    }

    /**
     * Ordre postfixe: une étape apparaît après celles dont elle consomme la sortie,
     * ce qui en fait un ordre d'exécution valide.
     *
     * @return list<Step>
     */
    public function stepsInExecutionOrder(): array
    {
        $steps = [];
        foreach ($this->roots as $root) {
            $this->collectSteps($root, $steps);
        }

        return $steps;
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

    /**
     * @param array<string, true> $seenIds
     */
    private function assertNode(Step|Ingredient $node, array &$seenIds): void
    {
        if ($node->id === '') {
            throw new \InvalidArgumentException('A recipe node must have a non-empty id.');
        }

        if (isset($seenIds[$node->id])) {
            throw new \InvalidArgumentException(\sprintf('Duplicate recipe node id "%s".', $node->id));
        }

        $seenIds[$node->id] = true;

        if ($node instanceof Ingredient) {
            if (trim($node->label) === '') {
                throw new \InvalidArgumentException(\sprintf('Ingredient "%s" must have a non-empty label.', $node->id));
            }

            return;
        }

        if (trim($node->text) === '') {
            throw new \InvalidArgumentException(\sprintf('Step "%s" must have a non-empty text.', $node->id));
        }

        foreach ($node->children as $child) {
            $this->assertNode($child, $seenIds);
        }
    }
}
