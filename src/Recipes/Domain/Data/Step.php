<?php

declare(strict_types=1);

namespace App\Recipes\Domain\Data;

final readonly class Step
{
    /**
     * @param list<Step|Ingredient> $children
     */
    public function __construct(
        public string $id,
        public string $text,
        public array $children,
    ) {
        if ($children === []) {
            throw new \InvalidArgumentException(\sprintf('Step "%s" must have at least one child.', $id));
        }
    }

    /**
     * Les ingrédients qui entrent directement dans cette étape.
     *
     * @return list<Ingredient>
     */
    public function ingredients(): array
    {
        return array_values(array_filter(
            $this->children,
            static fn (Step|Ingredient $child): bool => $child instanceof Ingredient,
        ));
    }

    /**
     * @return list<Step>
     */
    public function requiredSteps(): array
    {
        return array_values(array_filter(
            $this->children,
            static fn (Step|Ingredient $child): bool => $child instanceof Step,
        ));
    }
}
