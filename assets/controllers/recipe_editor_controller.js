import { Controller } from "@hotwired/stimulus";
import { createApp, h } from "vue";
import RecipeEditor from "../vue/RecipeEditor.vue";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["field", "mount"];
    static values = { content: Object, unitLabels: Object };

    connect() {
        this.app = createApp({
            render: () =>
                h(RecipeEditor, {
                    initial: this.contentValue,
                    unitLabels: this.unitLabelsValue,
                    onChange: (json) => {
                        this.fieldTarget.value = json;
                    },
                }),
        });
        this.app.mount(this.mountTarget);
    }

    disconnect() {
        this.app?.unmount();
        this.app = null;
    }
}
