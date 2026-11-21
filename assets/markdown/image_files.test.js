import { describe, expect, it } from "vitest";
import { imageFilesFrom } from "./image_files.js";

describe("imageFilesFrom", () => {
    it("keeps only image files", () => {
        const dataTransfer = { files: [{ type: "image/png" }, { type: "text/plain" }] };

        const files = imageFilesFrom(dataTransfer);

        expect(files).toEqual([{ type: "image/png" }]);
    });

    it("returns an empty list without data transfer", () => {
        const files = imageFilesFrom(null);

        expect(files).toEqual([]);
    });
});
