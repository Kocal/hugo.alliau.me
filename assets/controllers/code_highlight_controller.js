import { Controller } from "@hotwired/stimulus";
import { useIntersection } from "stimulus-use";
import { createHighlighterCore } from "shiki/core";
import { createOnigurumaEngine } from "shiki/engine/oniguruma";
import { transformerNotationDiff, transformerNotationHighlight } from "@shikijs/transformers";
import {
    createLanguageLoader,
    FALLBACK_LANGUAGE,
    resolveLanguage,
} from "../code_highlight/languages.js";

const highlighter = await createHighlighterCore({
    themes: [import("@shikijs/themes/light-plus"), import("@shikijs/themes/dark-plus")],
    langs: [],
    engine: createOnigurumaEngine(import("shiki/wasm")),
});
const loadLanguage = createLanguageLoader(highlighter);

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

        const language = resolveLanguage(this.langValue);
        if (language) {
            await loadLanguage(language);
        }

        this.element.outerHTML = await highlighter.codeToHtml(this.codeValue, {
            lang: language?.id ?? FALLBACK_LANGUAGE,
            themes: {
                light: "light-plus",
                dark: "dark-plus",
            },
            defaultColor: "light-dark()",
            transformers: [transformerNotationDiff(), transformerNotationHighlight()],
        });
    }
}
