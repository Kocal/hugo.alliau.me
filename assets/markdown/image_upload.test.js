import { describe, expect, it } from "vitest";
import { uploadImage } from "./image_upload.js";

const file = new File(["x"], "a.png", { type: "image/png" });

function options(fetch) {
    return { url: "/upload", slug: "my-post", csrfToken: "tok", fetch };
}

describe("uploadImage", () => {
    it("posts the file, slug and token and resolves the JSON body", async () => {
        let captured;
        const fetch = async (url, init) => {
            captured = { url, init };
            return { ok: true, status: 201, json: async () => ({ key: "k", width: 1, height: 2 }) };
        };

        const result = await uploadImage(file, options(fetch));

        expect(result).toEqual({ key: "k", width: 1, height: 2 });
        expect(captured.url).toBe("/upload");
        expect(captured.init.method).toBe("POST");
        expect(captured.init.body.get("slug")).toBe("my-post");
        expect(captured.init.body.get("_token")).toBe("tok");
        expect(captured.init.body.get("file")).toBe(file);
    });

    it("rejects with the server error message", async () => {
        const fetch = async () => ({
            ok: false,
            status: 415,
            json: async () => ({ error: "Nope" }),
        });

        const promise = uploadImage(file, options(fetch));

        await expect(promise).rejects.toThrow("Nope");
    });

    it("rejects with the HTTP status when the body is not JSON", async () => {
        const fetch = async () => ({
            ok: false,
            status: 500,
            json: async () => {
                throw new SyntaxError("Unexpected token <");
            },
        });

        const promise = uploadImage(file, options(fetch));

        await expect(promise).rejects.toThrow("Upload failed (HTTP 500)");
    });

    it("rejects when a 200 response is not JSON, as after a redirect to the login page", async () => {
        const fetch = async () => ({
            ok: true,
            status: 200,
            json: async () => {
                throw new SyntaxError("Unexpected token <");
            },
        });

        const promise = uploadImage(file, options(fetch));

        await expect(promise).rejects.toThrow("Upload failed (HTTP 200)");
    });

    it("rejects when a successful body has no key", async () => {
        const fetch = async () => ({ ok: true, status: 200, json: async () => ({}) });

        const promise = uploadImage(file, options(fetch));

        await expect(promise).rejects.toThrow("Upload failed (HTTP 200)");
    });
});
