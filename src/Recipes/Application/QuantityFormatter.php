<?php

declare(strict_types=1);

namespace App\Recipes\Application;

final readonly class QuantityFormatter
{
    private const float TOLERANCE = 0.01;

    /**
     * Les clés d'un tableau PHP ne peuvent pas être des flottants, d'où la liste de paires.
     *
     * @var list<array{float, string}>
     */
    private const array FRACTIONS = [
        [0.125, '⅛'],
        [0.25, '¼'],
        [1 / 3, '⅓'],
        [0.375, '⅜'],
        [0.5, '½'],
        [0.625, '⅝'],
        [2 / 3, '⅔'],
        [0.75, '¾'],
        [0.875, '⅞'],
    ];

    public function format(float $value, string $decimalSeparator = ','): string
    {
        $whole = (int) floor($value);
        $fraction = $value - $whole;

        if ($fraction < self::TOLERANCE || $fraction > 1 - self::TOLERANCE) {
            return (string) (int) round($value);
        }

        foreach (self::FRACTIONS as [$decimal, $glyph]) {
            if (abs($fraction - $decimal) < self::TOLERANCE) {
                return $whole > 0 ? $whole . ' ' . $glyph : $glyph;
            }
        }

        $formatted = number_format($value, 2, $decimalSeparator, '');

        return rtrim(rtrim($formatted, '0'), $decimalSeparator);
    }
}
