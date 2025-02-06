/**
 * @see https://github.com/codemirror/language-data/blob/main/src/language-data.ts
 */

import { LanguageDescription, LanguageSupport, StreamLanguage, StreamParser } from "@codemirror/language";

function legacy(parser: StreamParser<unknown>) {
    return new LanguageSupport(StreamLanguage.define(parser));
}

export const codeLanguages = [
    LanguageDescription.of({
        name: "JavaScript",
        alias: ["ecmascript", "js", "node"],
        extensions: ["js", "mjs", "cjs"],
        load() {
            return import("@codemirror/lang-javascript").then((m) => m.javascript());
        },
    }),
    LanguageDescription.of({
        name: "JSON",
        alias: ["json5"],
        extensions: ["json", "map"],
        load() {
            return import("@codemirror/lang-json").then((m) => m.json());
        },
    }),
    LanguageDescription.of({
        name: "Markdown",
        extensions: ["md", "markdown", "mkd"],
        load() {
            return import("@codemirror/lang-markdown").then((m) => m.markdown());
        },
    }),
    LanguageDescription.of({
        name: "PHP",
        extensions: ["php", "php3", "php4", "php5", "php7", "phtml"],
        load() {
            return import("@codemirror/lang-php").then((m) => m.php());
        },
    }),
    LanguageDescription.of({
        name: "HTML",
        extensions: ["html"],
        load() {
            return import("@codemirror/lang-html").then((m) => m.html());
        },
    }),
    LanguageDescription.of({
        name: "Css",
        extensions: ["css"],
        load() {
            return import("@codemirror/lang-css").then((m) => m.css());
        },
    }),
    LanguageDescription.of({
        name: "Sass",
        extensions: ["sass"],
        load() {
            return import("@codemirror/lang-sass").then((m) => m.sass({ indented: true }));
        },
    }),
    LanguageDescription.of({
        name: "SCSS",
        extensions: ["scss"],
        load() {
            return import("@codemirror/lang-sass").then((m) => m.sass());
        },
    }),
    LanguageDescription.of({
        name: "TSX",
        extensions: ["tsx"],
        load() {
            return import("@codemirror/lang-javascript").then((m) => m.javascript({ jsx: true, typescript: true }));
        },
    }),
    LanguageDescription.of({
        name: "TypeScript",
        alias: ["ts"],
        extensions: ["ts", "mts", "cts"],
        load() {
            return import("@codemirror/lang-javascript").then((m) => m.javascript({ typescript: true }));
        },
    }),
    LanguageDescription.of({
        name: "YAML",
        alias: ["yml"],
        extensions: ["yaml", "yml"],
        load() {
            return import("@codemirror/lang-yaml").then((m) => m.yaml());
        },
    }),
    LanguageDescription.of({
        name: "Vue",
        extensions: ["vue"],
        load() {
            return import("@codemirror/lang-vue").then((m) => m.vue());
        },
    }),
    LanguageDescription.of({
        name: "Shell",
        alias: ["bash", "sh", "zsh"],
        extensions: ["sh", "ksh", "bash"],
        filename: /^PKGBUILD$/,
        load() {
            return import("@codemirror/legacy-modes/mode/shell").then((m) => legacy(m.shell));
        },
    }),
    LanguageDescription.of({
        name: "Twig",
        extensions: ["twig"],
        load() {
            return import("./twig/index.ts").then((m) => m.twig());
        },
    }),
];
