import { describe, expect, it } from "vitest";
import { formatIngredientQuantity, formatQuantity } from "./quantity_formatter.js";

function formatters(locale) {
    return {
        rangeSeparator: " – ",
        pluralRules: new Intl.PluralRules(locale),
        numberFormat: new Intl.NumberFormat(locale, {
            maximumFractionDigits: 2,
            useGrouping: false,
        }),
    };
}

describe("formatQuantity", () => {
    it.each([
        [1, "1"],
        [200, "200"],
        [2.995, "3"],
        [3.005, "3"],
        [0.5, "½"],
        [1.5, "1 ½"],
        [1 / 3, "⅓"],
        [2 + 2 / 3, "2 ⅔"],
        [0.125, "⅛"],
        [0.4, "0.4"],
        [1.2345, "1.23"],
    ])("formats %f as %s", (value, expected) => {
        const { numberFormat } = formatters("en");

        const text = formatQuantity(value, numberFormat);

        expect(text).toBe(expected);
    });

    it("uses the decimal separator of the locale", () => {
        const { numberFormat } = formatters("fr");

        const text = formatQuantity(0.4, numberFormat);

        expect(text).toBe("0,4");
    });
});

describe("formatIngredientQuantity", () => {
    it("formats a single quantity without unit", () => {
        const quantity = { min: 1.5, max: null, unitOne: undefined, unitOther: undefined };

        const text = formatIngredientQuantity(quantity, formatters("en"));

        expect(text).toBe("1 ½");
    });

    it("joins a range with the separator", () => {
        const quantity = { min: 1, max: 2.5, unitOne: undefined, unitOther: undefined };

        const text = formatIngredientQuantity(quantity, formatters("en"));

        expect(text).toBe("1 – 2 ½");
    });

    it("uses the singular unit for one", () => {
        const quantity = { min: 1, max: null, unitOne: "tbsp", unitOther: "tbsps" };

        const text = formatIngredientQuantity(quantity, formatters("en"));

        expect(text).toBe("1 tbsp");
    });

    it("pluralizes the unit from the upper bound of a range", () => {
        const quantity = { min: 1, max: 2, unitOne: "tbsp", unitOther: "tbsps" };

        const text = formatIngredientQuantity(quantity, formatters("en"));

        expect(text).toBe("1 – 2 tbsps");
    });

    it("follows the plural rules of the locale", () => {
        const quantity = { min: 1.5, max: null, unitOne: "cuillère", unitOther: "cuillères" };

        const text = formatIngredientQuantity(quantity, formatters("fr"));

        expect(text).toBe("1 ½ cuillère");
    });

    it("leaves the unit out when a plural form is missing", () => {
        const quantity = { min: 2, max: null, unitOne: "tbsp", unitOther: undefined };

        const text = formatIngredientQuantity(quantity, formatters("en"));

        expect(text).toBe("2");
    });
});
