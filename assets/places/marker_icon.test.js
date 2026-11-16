import { describe, expect, it } from "vitest";
import { markerIconHtml } from "./marker_icon.js";

describe("markerIconHtml", () => {
    it("draws the place icon mask over the pin", () => {
        const iconMaskUri =
            "https://maps.gstatic.com/mapfiles/place_api/icons/v2/generic_pinlet.svg";

        const html = markerIconHtml(iconMaskUri);

        expect(html).toContain(`<img src="${iconMaskUri}" alt=""`);
    });
});
