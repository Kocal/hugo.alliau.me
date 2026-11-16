import { describe, expect, it } from "vitest";
import { minZoomFillingHeight } from "./min_zoom.js";

describe("minZoomFillingHeight", () => {
    it.each([
        [0, 0],
        [200, 0],
        [256, 0],
        [257, 1],
        [512, 1],
        [800, 2],
        [1024, 2],
        [1025, 3],
    ])("a map %i px high needs a zoom of at least %i", (height, expected) => {
        const zoom = minZoomFillingHeight(height);

        expect(zoom).toBe(expected);
    });
});
