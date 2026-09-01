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
}
