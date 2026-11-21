import { describe, expect, it, vi } from "vitest";
import { createLanguageLoader, FALLBACK_LANGUAGE, resolveLanguage } from "./languages.js";

describe("resolveLanguage", () => {
    it.each([
        ["php", "php"],
        ["bash", "shellscript"],
        ["shell", "shellscript"],
        ["sh", "shellscript"],
        ["js", "javascript"],
        ["dotenv", "dotenv"],
        ["markdown", "markdown"],
        ["caddyfile", null],
        ["txt", null],
        ["", null],
    ])("maps %j to %j", (lang, expected) => {
        const language = resolveLanguage(lang);

        expect(language?.id ?? null).toBe(expected);
    });

    it("returns a loader for a known language", async () => {
        const loaders = { shellscript: () => Promise.resolve("grammar") };

        const language = resolveLanguage("bash", { loaders, aliases: { bash: "shellscript" } });

        expect(language.id).toBe("shellscript");
        await expect(language.load()).resolves.toBe("grammar");
    });

    it("falls back to plain text", () => {
        expect(FALLBACK_LANGUAGE).toBe("text");
    });
});

describe("createLanguageLoader", () => {
    it("loads a grammar once even when requested concurrently", async () => {
        const load = vi.fn(() => Promise.resolve({ default: [{ name: "sql" }] }));
        const highlighter = {
            getLoadedLanguages: () => [],
            loadLanguage: vi.fn(() => Promise.resolve()),
        };
        const loadLanguage = createLanguageLoader(highlighter);

        await Promise.all([loadLanguage({ id: "sql", load }), loadLanguage({ id: "sql", load })]);

        expect(load).toHaveBeenCalledTimes(1);
        expect(highlighter.loadLanguage).toHaveBeenCalledTimes(1);
    });

    it("skips a grammar the highlighter already has", async () => {
        const load = vi.fn();
        const highlighter = { getLoadedLanguages: () => ["php"], loadLanguage: vi.fn() };
        const loadLanguage = createLanguageLoader(highlighter);

        await loadLanguage({ id: "php", load });

        expect(load).not.toHaveBeenCalled();
    });
});
