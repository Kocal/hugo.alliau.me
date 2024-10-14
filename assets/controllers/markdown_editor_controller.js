import {Controller} from "@hotwired/stimulus";
import {basicSetup, EditorView} from "codemirror"
import {markdown} from "@codemirror/lang-markdown"
import {dracula, tomorrow} from "thememirror";
import {Compartment} from "@codemirror/state";
import {codeLanguages} from "../codemirror/code_languages.js";
import {lightDarkThemeSwitcher} from "../codemirror/light_dark_theme_switcher.js";


/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = [
        'textarea',
        'btn-bold',
        'btn-italic',
        'btn-strikethrough',
    ]

    connect() {
        const editorTheme = new Compartment();
        
        this.view = new EditorView({
            doc: this.textareaTarget.value,
            extensions: [
                basicSetup,
                markdown({codeLanguages}),
                lightDarkThemeSwitcher({darkTheme: dracula, lightTheme: tomorrow}),
                EditorView.updateListener.of(viewUpdate => {
                    this.textareaTarget.value = viewUpdate.view.state.doc.toString();
                }),
            ],
        })
        // this.view.dom.style.width = this.textareaTarget.clientWidth + 'px';
        this.view.dom.style.maxHeight = '85dvh'; // hard-coded, can be improved later
        this.textareaTarget.parentNode.insertBefore(this.view.dom, this.textareaTarget)
        this.textareaTarget.style.display = "none";
    }

    disconnect() {
        this.view.destroy()
    }
}
