import { redo as commandRedo, undo as commandUndo } from "@codemirror/commands";
import { markdown } from "@codemirror/lang-markdown";
import { Compartment, EditorSelection } from "@codemirror/state";
import { githubDark } from "@fsegurai/codemirror-theme-github-dark";
import { githubLight } from "@fsegurai/codemirror-theme-github-light";
import { Controller } from "@hotwired/stimulus";
import { basicSetup, EditorView } from "codemirror";
import { codeLanguages } from "../codemirror/code_languages.js";
import { lightDarkThemeSwitcher } from "../codemirror/light_dark_theme_switcher.js";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["textarea"];
    static classes = ["editor"];

    connect() {
        const editorTheme = new Compartment();

        this.view = new EditorView({
            doc: this.textareaTarget.value,
            extensions: [
                basicSetup,
                markdown({ codeLanguages }),
                EditorView.lineWrapping,
                EditorView.editorAttributes.of({
                    class: [...this.editorClasses].join(" "),
                }),
                EditorView.theme({
                    "&.cm-focused": {
                        outline: "none",
                    },
                }),
                lightDarkThemeSwitcher({
                    darkTheme: githubDark,
                    lightTheme: githubLight,
                }),
                EditorView.updateListener.of((viewUpdate) => {
                    this.textareaTarget.value = viewUpdate.view.state.doc.toString();
                }),
            ],
        });

        this.view.dom.style.maxHeight = "80dvh"; // hard-coded, can be improved later
        this.textareaTarget.parentNode.insertBefore(this.view.dom, this.textareaTarget);
        this.textareaTarget.style.display = "none";

        window.addEventListener("resize", this.onWindowResize.bind(this));
    }

    disconnect() {
        window.removeEventListener("resize", this.onWindowResize.bind(this));
        this.view.destroy();
    }

    onWindowResize() {
        this.view.requestMeasure();
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
            throw new Error('Argument "level" is required and must be a number.');
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
        const type = typeof event.params.type === "string" ? event.params.type : "unordered";
        if (type !== "unordered" && type !== "ordered") {
            throw new Error('Argument "type" must be either "ordered" or "unordered".');
        }
        this.#insertSyntax(`${type === "unordered" ? "-" : "1."} \${cursor}`);
    }

    alert(event) {
        const type = typeof event.params.type === "string" ? event.params.type : "info";
        const validTypes = ["info", "tip", "warning", "danger"];
        if (!validTypes.includes(type)) {
            throw new Error(`Argument "type" must be one of ${validTypes.join(", ")}.`);
        }

        this.#insertSyntax(`::: ${type}\n\${cursor}\n:::\n`);
    }

    async chatGptTranslate() {
        await navigator.clipboard.writeText(`I want you to act as a translator, spelling corrector and improver. 
Your role is to translate the text from English to French or from French to English, and correct and improve the text.
If you didn't translated the text, then you failed the task. You MUST translate the text.
Sometimes two languages can be found in the text, if you detect that, you should ignore the less dominant language and translate the rest of the text in the other language.
You must replace my simplified A0-level words and sentences with more beautiful and elegant, upper level words and sentences. 
You must keep the meaning same, but make them more literary. 
I want you to only reply the correction, the improvements and nothing else, do not write explanations.
The source text is in Markdown format, you should only correct the text and ignore the code blocks (inside single backticks or triple backticks), but you can translate comments inside code blocks.
The source text can be found after "@@@source@@@" and before "@@@end@@@".

@@@source@@@
${this.view.state.doc.toString()}
@@@end@@@`);
        window.open("https://chat.openai.com/", "_blank");
    }

    async chatGptSpellcheck() {
        await navigator.clipboard.writeText(`I want you to act as a spelling corrector and improver. 
Your role is to correct and improve the text.
I want you to replace my simplified A0-level words and sentences with more beautiful and elegant, upper level words and sentences. 
Keep the meaning same, but make them more literary. 
I want you to only reply the correction, the improvements and nothing else, do not write explanations.
The source text is in Markdown format, you should only correct the text and ignore the code blocks (inside single backticks or triple backticks), but you can translate comments inside code blocks.
The source text can be found after "@@@source@@@" and before "@@@end@@@".

@@@source@@@
${this.view.state.doc.toString()}
@@@end@@@`);
        window.open("https://chat.openai.com/", "_blank");
    }

    #insertSyntax(text) {
        const cursorId = "${cursor}";
        const cursorIndex = text.indexOf(cursorId);
        if (cursorIndex === -1) {
            throw new Error(`Text must include the cursor placeholder: ${cursorId}`);
        }

        const textSanitized = text.replace(cursorId, "");

        this.view.dispatch(
            this.view.state.changeByRange((range) => ({
                changes: [{ from: range.from, insert: textSanitized }],
                range: EditorSelection.range(range.from, range.to + textSanitized.length),
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
}
