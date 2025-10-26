import {Controller} from "@hotwired/stimulus"
import {codeToHtml} from 'https://esm.sh/shiki@3.13.0'
import {transformerNotationDiff, transformerNotationHighlight} from 'https://esm.sh/@shikijs/transformers@3.13.0'
import {useIntersection} from 'stimulus-use'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        code: String,
        lang: String,
    };

    connect() {
        useIntersection(this)
    }

    async appear() {
        if (this.rendered) {
            return;
        }

        this.rendered = true

        this.element.outerHTML = await codeToHtml(this.codeValue, {
            lang: this.langValue || 'plaintext',
            theme: 'one-dark-pro',
            transformers: [
                transformerNotationDiff(),
                transformerNotationHighlight(),
            ]
        });
    }
}
