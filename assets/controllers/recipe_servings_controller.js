import { Controller } from "@hotwired/stimulus";
import { formatIngredientQuantity } from "../recipes/quantity_formatter.js";

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

            quantity.textContent = formatIngredientQuantity(
                {
                    min,
                    max,
                    unitOne: element.dataset.unitOne,
                    unitOther: element.dataset.unitOther,
                },
                {
                    rangeSeparator: this.rangeSeparatorValue,
                    pluralRules: this.pluralRules,
                    numberFormat: this.numberFormat,
                },
            );
        }
    }
}
