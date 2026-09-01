import { Controller } from "@hotwired/stimulus";

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

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["input"];
    static values = { base: Number, rangeSeparator: String };

    connect() {
        this.locale = document.documentElement.lang || "en";
        this.pluralRules = new Intl.PluralRules(this.locale);
        this.numberFormat = new Intl.NumberFormat(this.locale, {
            maximumFractionDigits: 2,
            useGrouping: false,
        });
        this.update();
    }

    decrease() {
        this.inputTarget.value = Math.max(1, Number(this.inputTarget.value) - 1);
        this.update();
    }

    increase() {
        this.inputTarget.value = Number(this.inputTarget.value) + 1;
        this.update();
    }

    update() {
        const servings = Number(this.inputTarget.value);
        if (!Number.isFinite(servings) || servings < 1 || !this.baseValue) return;

        const factor = servings / this.baseValue;

        for (const element of this.element.querySelectorAll("[data-qty-min]")) {
            const quantity = element.querySelector("[data-quantity]");
            if (!quantity) continue;

            const min = Number(element.dataset.qtyMin) * factor;
            const max = element.dataset.qtyMax ? Number(element.dataset.qtyMax) * factor : null;

            let text =
                max === null
                    ? this._format(min)
                    : this._format(min) + this.rangeSeparatorValue + this._format(max);

            const one = element.dataset.unitOne;
            const other = element.dataset.unitOther;
            if (one && other) {
                text += " " + (this.pluralRules.select(max ?? min) === "one" ? one : other);
            }

            quantity.textContent = text;
        }
    }

    _format(value) {
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

        return this.numberFormat.format(value);
    }
}
