import { Controller } from "@hotwired/stimulus";
import { useIntersection } from "stimulus-use";
import { createHighlighterCore } from "shiki/core";
import { createOnigurumaEngine } from "shiki/engine/oniguruma";
import { transformerNotationDiff, transformerNotationHighlight } from "@shikijs/transformers";

const highlighter = await createHighlighterCore({
    themes: [import("@shikijs/themes/light-plus"), import("@shikijs/themes/dark-plus")],
    langs: [
        import("@shikijs/langs/html"),
        import("@shikijs/langs/css"),
        import("@shikijs/langs/javascript"),
        import("@shikijs/langs/php"),
        import("@shikijs/langs/twig"),
        import("@shikijs/langs/json"),
        import("@shikijs/langs/yaml"),
    ],
    engine: createOnigurumaEngine(import("shiki/wasm")),
});

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        code: String,
        lang: String,
    };

    connect() {
        useIntersection(this);
    }

    async appear() {
        if (this.rendered) {
            return;
        }

        this.rendered = true;

        this.element.outerHTML = await highlighter.codeToHtml(this.codeValue, {
            lang: this.langValue || "plaintext",
            themes: {
                light: "light-plus",
                dark: "dark-plus",
            },
            defaultColor: "light-dark()",
            transformers: [transformerNotationDiff(), transformerNotationHighlight()],
        });
    }
}
