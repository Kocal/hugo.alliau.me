<?php

declare(strict_types=1);

namespace App\Recipes\Domain\Data;

use Symfony\Component\Translation\TranslatableMessage;
use function Symfony\Component\Translation\t;

/**
 * Un ingrédient qui se compte sans unité ("2 oignons", "½ concombre") a `unit = null`,
 * il n'existe donc volontairement pas de cas "PIECE".
 */
enum Unit: string
{
    case G = 'g';

    case KG = 'kg';

    case ML = 'ml';

    case CL = 'cl';

    case L = 'l';

    case TBSP = 'tbsp';

    case TSP = 'tsp';

    case PINCH = 'pinch';

    case CLOVE = 'clove';

    case SLICE = 'slice';

    case BUNCH = 'bunch';

    case SPRIG = 'sprig';

    case HANDFUL = 'handful';

    case DROP = 'drop';

    case CAN = 'can';

    case PACK = 'pack';

    public function toTranslatable(float $count): TranslatableMessage
    {
        return t('unit.' . $this->value, [
            'count' => $count,
        ]);
    }
}
