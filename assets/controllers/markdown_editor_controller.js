import {Controller} from "@hotwired/stimulus";
import {basicSetup, EditorView} from "codemirror"
import {markdown} from "@codemirror/lang-markdown"
import {dracula, tomorrow} from "thememirror";
import {Compartment, EditorSelection} from "@codemirror/state";
import {undo as commandUndo, redo as commandRedo} from '@codemirror/commands';
import {codeLanguages} from "../codemirror/code_languages.js";
import {lightDarkThemeSwitcher} from "../codemirror/light_dark_theme_switcher.js";


/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = [
        'textarea',
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
    
    undo() {
        commandUndo(this.view)
        this.view.focus();
    }
    
    redo() {
        commandRedo(this.view)
        this.view.focus();
    }

    bold() {
        this.view.dispatch(this.view.state.changeByRange(range => ({
            changes: [{from: range.from, insert: "**"}, {from: range.to, insert: "**"}],
            range: EditorSelection.range(range.from, range.to + 4)
        })));
        this.view.dispatch({
            selection: {
                anchor: this.view.state.selection.ranges[0].from + 2,
                head: this.view.state.selection.ranges[0].from + 2,
            }
        })
        this.view.focus();
    }
    italic() {
        this.view.dispatch(this.view.state.changeByRange(range => ({
            changes: [{from: range.from, insert: "_"}, {from: range.to, insert: "_"}],
            range: EditorSelection.range(range.from, range.to + 2)
        })));
        this.view.dispatch({ 
            selection: {
                anchor: this.view.state.selection.ranges[0].from + 1,
                head: this.view.state.selection.ranges[0].from + 1,
            }
        })
        this.view.focus();
    }
    strikethrough() {
        this.view.dispatch(this.view.state.changeByRange(range => ({
            changes: [{from: range.from, insert: "~"}, {from: range.to, insert: "~"}],
            range: EditorSelection.range(range.from, range.to + 2)
        })));

        this.view.dispatch({
            selection: {
                anchor: this.view.state.selection.ranges[0].from + 1,
                head: this.view.state.selection.ranges[0].from + 1,
            }
        })
        this.view.focus();
    }

    disconnect() {
        this.view.destroy()
    }
}
