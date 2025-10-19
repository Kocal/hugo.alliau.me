import { Controller } from "@hotwired/stimulus"
import { codeToHtml } from 'https://esm.sh/shiki@3.13.0'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    async connect() {
        this.element.outerHTML = await codeToHtml(this.element.textContent, {
            lang: this.element.dataset.lang || 'plaintext',
            theme: 'one-dark-pro',
        });
    }
}
