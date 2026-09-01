<?php

declare(strict_types=1);

namespace App\Recipes\Infrastructure\Doctrine\DBAL\Type;

use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Domain\Data\Step;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

final class RecipeContentType extends Type
{
    public const string NAME = 'recipe_content';

    private ?TreeMapper $mapper = null;

    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'JSONB';
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?RecipeContent
    {
        if ($value instanceof RecipeContent || $value === null) {
            return $value;
        }

        if (! \is_string($value)) {
            throw InvalidType::new($value, self::NAME, ['null', 'string', RecipeContent::class]);
        }

        try {
            $decoded = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);

            if (! \is_array($decoded)) {
                throw new \InvalidArgumentException('Decoded recipe content must be a JSON object.');
            }

            $roots = $decoded['roots'] ?? [];

            if (! \is_array($roots)) {
                throw new \InvalidArgumentException('"roots" must be an array.');
            }

            foreach ($roots as $node) {
                $this->assertNodeShapeMatchesDiscriminator($node);
            }

            return $this->mapper()
                ->map(RecipeContent::class, $decoded);
        } catch (MappingError|\InvalidArgumentException|\JsonException $error) {
            // \InvalidArgumentException couvre nos propres rejets du discriminant "type" effectués
            // avant le passage à Valinor, ainsi que les invariants des constructeurs du domaine
            // (Step sans enfant, id manquant/dupliqué/vide, texte ou libellé vide) remontés tel
            // quels par Valinor lors de la construction de l'arbre.
            throw ValueNotConvertible::new($value, self::NAME, null, $error);
        }
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! $value instanceof RecipeContent) {
            throw InvalidType::new($value, self::NAME, ['null', RecipeContent::class]);
        }

        return json_encode([
            'roots' => array_map($this->normalizeNode(...), $value->roots),
        ], \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeNode(Step|Ingredient $node): array
    {
        if ($node instanceof Ingredient) {
            return [
                'type' => 'ingredient',
                'id' => $node->id,
                'label' => $node->label,
                'min' => $node->min,
                'max' => $node->max,
                'unit' => $node->unit?->value,
            ];
        }

        return [
            'type' => 'step',
            'id' => $node->id,
            'text' => $node->text,
            'children' => array_map($this->normalizeNode(...), $node->children),
        ];
    }

    /**
     * Rejette toute divergence entre le discriminant "type" et la forme réelle du nœud,
     * pour que la résolution structurelle de Valinor ne puisse plus jamais être ambiguë.
     * Les invariants du domaine (id manquant/vide/dupliqué, texte/libellé vide, étape sans
     * enfant) sont eux enforcés par les constructeurs que Valinor appelle ensuite.
     */
    private function assertNodeShapeMatchesDiscriminator(mixed $node): void
    {
        if (! \is_array($node)) {
            throw new \InvalidArgumentException('Each recipe node must be a JSON object.');
        }

        $type = $node['type'] ?? null;

        if ($type === 'step') {
            if (! \array_key_exists('text', $node) || ! \array_key_exists('children', $node)) {
                throw new \InvalidArgumentException('A "step" node must declare "text" and "children".');
            }

            if (\array_key_exists('label', $node) || \array_key_exists('min', $node) || \array_key_exists('max', $node) || \array_key_exists('unit', $node)) {
                throw new \InvalidArgumentException('A "step" node must not declare "label", "min", "max" or "unit".');
            }

            if (! \is_array($node['children'])) {
                throw new \InvalidArgumentException('A "step" node\'s "children" must be an array.');
            }

            foreach ($node['children'] as $child) {
                $this->assertNodeShapeMatchesDiscriminator($child);
            }

            return;
        }

        if ($type === 'ingredient') {
            if (! \array_key_exists('label', $node)) {
                throw new \InvalidArgumentException('An "ingredient" node must declare "label".');
            }

            if (\array_key_exists('text', $node) || \array_key_exists('children', $node)) {
                throw new \InvalidArgumentException('An "ingredient" node must not declare "text" or "children".');
            }

            return;
        }

        throw new \InvalidArgumentException(\sprintf(
            'Unknown recipe node type %s.',
            \is_string($type) ? \sprintf('"%s"', $type) : \get_debug_type($type),
        ));
    }

    private function mapper(): TreeMapper
    {
        return $this->mapper ??= new MapperBuilder()
            ->allowSuperfluousKeys()
            ->mapper();
    }
}
