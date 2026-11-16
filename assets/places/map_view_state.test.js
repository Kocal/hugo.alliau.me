import { describe, expect, it } from "vitest";
import { formatMapViewHash, parseMapViewHash, toMapViewState } from "./map_view_state.js";

describe("parseMapViewHash", () => {
    it("reads the zoom and the center", () => {
        const hash = "#z=5&center=48.85661%2C2.35222";

        const view = parseMapViewHash(hash);

        expect(view).toEqual({ zoom: 5, center: [48.85661, 2.35222] });
    });

    it.each([
        ["an empty hash", ""],
        ["a hash that is not a view state", "#places"],
        ["a missing center", "#z=5"],
        ["a missing zoom", "#center=48.85661,2.35222"],
        ["a zoom that is not a number", "#z=far&center=48.85661,2.35222"],
        ["a center with a single coordinate", "#z=5&center=48.85661"],
        ["a center that is not a number", "#z=5&center=north,east"],
    ])("returns null for %s", (_label, hash) => {
        const view = parseMapViewHash(hash);

        expect(view).toBeNull();
    });
});

describe("toMapViewState", () => {
    it("rounds the center to 5 decimals", () => {
        const center = { lat: 48.856613, lng: 2.352222 };

        const state = toMapViewState(5, center);

        expect(state).toEqual({ z: 5, center: ["48.85661", "2.35222"] });
    });
});

describe("formatMapViewHash", () => {
    it("serializes the state as a URL hash", () => {
        const state = toMapViewState(12, { lat: 37.55137, lng: 126.98821 });

        const hash = formatMapViewHash(state);

        expect(hash).toBe("#z=12&center=37.55137%2C126.98821");
    });

    it("writes a hash that parseMapViewHash reads back", () => {
        const hash = formatMapViewHash(toMapViewState(12, { lat: 37.55137, lng: 126.98821 }));

        const view = parseMapViewHash(hash);

        expect(view).toEqual({ zoom: 12, center: [37.55137, 126.98821] });
    });
});
