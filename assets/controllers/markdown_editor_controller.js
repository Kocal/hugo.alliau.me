import { redo as commandRedo, undo as commandUndo } from "@codemirror/commands";
import { markdown } from "@codemirror/lang-markdown";
import { Compartment, EditorSelection } from "@codemirror/state";
import { githubDark } from "@fsegurai/codemirror-theme-github-dark";
import { githubLight } from "@fsegurai/codemirror-theme-github-light";
import { Controller } from "@hotwired/stimulus";
import { basicSetup, EditorView } from "codemirror";
import { codeLanguages } from "../codemirror/code_languages.js";
import { lightDarkThemeSwitcher } from "../codemirror/light_dark_theme_switcher.js";
import { imageFilesFrom } from "../markdown/image_files.js";
import { altFromFilename, imageSnippet, uploadingPlaceholder } from "../markdown/image_snippet.js";
import { uploadImage } from "../markdown/image_upload.js";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["textarea", "error"];
    static classes = ["editor"];
    static values = { uploadUrl: String, csrfToken: String, slugInputSelector: String };

    connect() {
        this.fileInput = document.createElement("input");
        this.fileInput.type = "file";
        this.fileInput.accept = "image/*";
        this.fileInput.multiple = true;
        this.fileInput.hidden = true;
        this.fileInput.addEventListener("change", this.onFileInputChange);
        this.element.append(this.fileInput);

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
                EditorView.domEventHandlers({
                    drop: (event, view) => {
                        const position = view.posAtCoords({ x: event.clientX, y: event.clientY });

                        return this.#handleImageTransfer(event, event.dataTransfer, position);
                    },
                    paste: (event) => this.#handleImageTransfer(event, event.clipboardData),
                }),
                EditorView.updateListener.of((viewUpdate) => {
                    this.textareaTarget.value = viewUpdate.view.state.doc.toString();
                }),
            ],
        });

        this.view.dom.style.maxHeight = "80dvh"; // hard-coded, can be improved later
        this.textareaTarget.parentNode.insertBefore(this.view.dom, this.textareaTarget);
        this.textareaTarget.style.display = "none";

        window.addEventListener("resize", this.onWindowResize);
    }

    disconnect() {
        this.fileInput.remove();
        window.removeEventListener("resize", this.onWindowResize);
        this.view.destroy();
    }

    onWindowResize = () => {
        this.view.requestMeasure();
    };

    onFileInputChange = () => {
        const files = Array.from(this.fileInput.files);
        this.fileInput.value = "";
        this.#uploadFiles(files);
    };

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

    uploadImage() {
        this.fileInput.click();
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
        await navigator.clipboard
            .writeText(`I want you to act as a translator, spelling corrector and improver. 
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
        await navigator.clipboard
            .writeText(`I want you to act as a spelling corrector and improver. 
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

    #handleImageTransfer(event, dataTransfer, position = null) {
        const files = imageFilesFrom(dataTransfer);
        if (!files.length) {
            return false;
        }

        event.preventDefault();
        this.#uploadFiles(files, position);

        return true;
    }

    async #uploadFiles(files, position = null) {
        const slug = document.querySelector(this.slugInputSelectorValue)?.value.trim();
        if (!slug) {
            this.#showError("Set the slug before adding an image.");
            return;
        }

        let cursor = position ?? this.view.state.selection.main.head;
        const uploads = files.map((file) => {
            const placeholder = uploadingPlaceholder(file.name);
            this.view.dispatch({ changes: { from: cursor, insert: placeholder } });
            cursor += placeholder.length;

            return this.#uploadAndReplace(file, placeholder, slug);
        });

        await Promise.all(uploads);
    }

    async #uploadAndReplace(file, placeholder, slug) {
        try {
            const result = await uploadImage(file, {
                url: this.uploadUrlValue,
                slug,
                csrfToken: this.csrfTokenValue,
            });
            this.#replacePlaceholder(
                placeholder,
                imageSnippet({ alt: altFromFilename(file.name), ...result }),
            );
            this.#clearError();
        } catch (error) {
            this.#replacePlaceholder(placeholder, "");
            this.#showError(error.message);
        }
    }

    #replacePlaceholder(placeholder, replacement) {
        const from = this.view.state.doc.toString().indexOf(placeholder);
        if (from === -1) {
            return;
        }

        this.view.dispatch({
            changes: { from, to: from + placeholder.length, insert: replacement },
        });
    }

    #showError(message) {
        this.errorTarget.textContent = message;
        this.errorTarget.hidden = false;
    }

    #clearError() {
        this.errorTarget.textContent = "";
        this.errorTarget.hidden = true;
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
