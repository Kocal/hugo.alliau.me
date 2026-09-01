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
