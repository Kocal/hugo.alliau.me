const loaders = {
    css: () => import("@shikijs/langs/css"),
    dotenv: () => import("@shikijs/langs/dotenv"),
    gherkin: () => import("@shikijs/langs/gherkin"),
    html: () => import("@shikijs/langs/html"),
    javascript: () => import("@shikijs/langs/javascript"),
    json: () => import("@shikijs/langs/json"),
    markdown: () => import("@shikijs/langs/markdown"),
    php: () => import("@shikijs/langs/php"),
    ruby: () => import("@shikijs/langs/ruby"),
    shellscript: () => import("@shikijs/langs/shellscript"),
    sql: () => import("@shikijs/langs/sql"),
    twig: () => import("@shikijs/langs/twig"),
    yaml: () => import("@shikijs/langs/yaml"),
};

const aliases = {
    bash: "shellscript",
    env: "dotenv",
    js: "javascript",
    md: "markdown",
    sh: "shellscript",
    shell: "shellscript",
    yml: "yaml",
    zsh: "shellscript",
};

export const FALLBACK_LANGUAGE = "text";

export function resolveLanguage(
    lang,
    { loaders: knownLoaders = loaders, aliases: knownAliases = aliases } = {},
) {
    const id = knownAliases[lang] ?? lang;

    return id in knownLoaders ? { id, load: knownLoaders[id] } : null;
}

export function createLanguageLoader(highlighter) {
    const pending = new Map();

    return async ({ id, load }) => {
        if (highlighter.getLoadedLanguages().includes(id)) {
            return;
        }
        if (!pending.has(id)) {
            pending.set(
                id,
                load().then((grammar) => highlighter.loadLanguage(grammar)),
            );
        }

        await pending.get(id);
    };
}
