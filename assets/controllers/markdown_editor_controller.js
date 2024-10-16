import { redo as commandRedo, undo as commandUndo } from "@codemirror/commands";
import { markdown } from "@codemirror/lang-markdown";
import { Compartment, EditorSelection } from "@codemirror/state";
import { Controller } from "@hotwired/stimulus";
import { EditorView, basicSetup } from "codemirror";
import { dracula, tomorrow } from "thememirror";
import { codeLanguages } from "../codemirror/code_languages.js";
import { lightDarkThemeSwitcher } from "../codemirror/light_dark_theme_switcher.js";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["textarea"];

    connect() {
        const editorTheme = new Compartment();

        this.view = new EditorView({
            doc: this.textareaTarget.value,
            extensions: [
                basicSetup,
                markdown({ codeLanguages }),
                lightDarkThemeSwitcher({
                    darkTheme: dracula,
                    lightTheme: tomorrow,
                }),
                EditorView.updateListener.of((viewUpdate) => {
                    this.textareaTarget.value =
                        viewUpdate.view.state.doc.toString();
                }),
            ],
        });
        // this.view.dom.style.width = this.textareaTarget.clientWidth + 'px';
        this.view.dom.style.maxHeight = "85dvh"; // hard-coded, can be improved later
        this.textareaTarget.parentNode.insertBefore(
            this.view.dom,
            this.textareaTarget,
        );
        this.textareaTarget.style.display = "none";
    }

    undo() {
        commandUndo(this.view);
        this.view.focus();
    }

    redo() {
        commandRedo(this.view);
        this.view.focus();
    }

    bold() {
        this.#insertSyntax("**${cursor}**");
    }

    italic() {
        this.#insertSyntax("_${cursor}_");
    }

    strikethrough() {
        this.#insertSyntax("~~${cursor}~~");
    }

    /**
     * TODO: can be improved to ensure the header is added at the start of the line
     */
    header(event) {
        const { level } = event.params;
        if (typeof level !== "number") {
            throw new Error(
                'Argument "level" is required and must be a number.',
            );
        }

        this.#insertSyntax(`${"#".repeat(level)} \${cursor}`);
    }

    /**
     * TODO: can be improved to add a link to the selected text
     */
    link() {
        this.#insertSyntax("[${cursor}](https://)");
    }

    image() {
        this.#insertSyntax("![](${cursor})(https://)");
    }

    codeBlock() {
        this.#insertSyntax("```lang [file.ext]\n${cursor}\n```");
    }

    quote() {
        this.#insertSyntax("> ${cursor}");
    }

    list(event) {
        const type =
            typeof event.params.type === "string"
                ? event.params.type
                : "unordered";
        if (type !== "unordered" && type !== "ordered") {
            throw new Error(
                'Argument "type" must be either "ordered" or "unordered".',
            );
        }
        this.#insertSyntax(`${type === "unordered" ? "-" : "1."} \${cursor}`);
    }

    alert(event) {
        const type =
            typeof event.params.type === "string" ? event.params.type : "info";
        const validTypes = ["info", "tip", "warning", "important", "caution"];
        if (!validTypes.includes(type)) {
            throw new Error(
                `Argument "type" must be one of ${validTypes.join(", ")}.`,
            );
        }

        this.#insertSyntax(`> [!${type.toUpperCase()}]\n> \${cursor}`);
    }
    
    #insertSyntax(text) {
        const cursorId = "${cursor}";
        const cursorIndex = text.indexOf(cursorId);
        if (cursorIndex === -1) {
            throw new Error(`Text must include the cursor placeholder: ${cursorId}`);
        }
    
        text = text.replace(cursorId, "");
        
        this.view.dispatch(
            this.view.state.changeByRange((range) => ({
                changes: [{ from: range.from, insert: text }],
                range: EditorSelection.range(
                    range.from,
                    range.to + text.length,
                ),
            })),
        );
        
        this.view.dispatch({
            selection: {
                anchor: this.view.state.selection.ranges[0].from + cursorIndex,
                head: this.view.state.selection.ranges[0].from + cursorIndex,
            },
        });
        
        this.view.focus();
    }

    disconnect() {
        this.view.destroy();
    }
}
