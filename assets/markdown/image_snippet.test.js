import { describe, expect, it } from "vitest";
import { altFromFilename, imageSnippet, uploadingPlaceholder } from "./image_snippet.js";

describe("imageSnippet", () => {
    it("adds the attribute block when dimensions are known", () => {
        const input = { alt: "Config", key: "posts-assets/a/b.png", width: 1200, height: 630 };

        const snippet = imageSnippet(input);

        expect(snippet).toBe("![Config](posts-assets/a/b.png){width=1200 height=630}");
    });

    it.each([
        [null, null],
        [1200, null],
        [null, 630],
    ])("omits the attribute block when dimensions are %s x %s", (width, height) => {
        const input = { alt: "Config", key: "posts-assets/a/b.svg", width, height };

        const snippet = imageSnippet(input);

        expect(snippet).toBe("![Config](posts-assets/a/b.svg)");
    });
});

describe("altFromFilename", () => {
    it("drops the extension and turns separators into spaces", () => {
        const alt = altFromFilename("ma_config-coolify.PNG");

        expect(alt).toBe("ma config coolify");
    });
});

describe("uploadingPlaceholder", () => {
    it("wraps the filename in an empty image", () => {
        const placeholder = uploadingPlaceholder("a.png");

        expect(placeholder).toBe("![Uploading a.png…]()");
    });
});
