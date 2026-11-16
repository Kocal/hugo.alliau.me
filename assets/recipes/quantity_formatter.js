const FRACTIONS = [
    [0.125, "⅛"],
    [0.25, "¼"],
    [1 / 3, "⅓"],
    [0.375, "⅜"],
    [0.5, "½"],
    [0.625, "⅝"],
    [2 / 3, "⅔"],
    [0.75, "¾"],
    [0.875, "⅞"],
];
const TOLERANCE = 0.01;

export function formatQuantity(value, numberFormat) {
    const whole = Math.floor(value);
    const fraction = value - whole;

    if (fraction < TOLERANCE || fraction > 1 - TOLERANCE) {
        return String(Math.round(value));
    }

    for (const [decimal, glyph] of FRACTIONS) {
        if (Math.abs(fraction - decimal) < TOLERANCE) {
            return whole > 0 ? `${whole} ${glyph}` : glyph;
        }
    }

    return numberFormat.format(value);
}

export function formatIngredientQuantity(
    { min, max, unitOne, unitOther },
    { rangeSeparator, pluralRules, numberFormat },
) {
    let text =
        max === null
            ? formatQuantity(min, numberFormat)
            : formatQuantity(min, numberFormat) +
              rangeSeparator +
              formatQuantity(max, numberFormat);

    if (unitOne && unitOther) {
        text += " " + (pluralRules.select(max ?? min) === "one" ? unitOne : unitOther);
    }

    return text;
}
