export async function uploadImage(file, { url, slug, csrfToken, fetch = globalThis.fetch }) {
    const body = new FormData();
    body.append("file", file);
    body.append("slug", slug);
    body.append("_token", csrfToken);

    const response = await fetch(url, { method: "POST", body });

    let payload = null;
    try {
        payload = await response.json();
    } catch {
        payload = null;
    }

    if (!response.ok) {
        throw new Error(payload?.error ?? `Upload failed (HTTP ${response.status})`);
    }

    if (typeof payload?.key !== "string") {
        throw new Error(`Upload failed (HTTP ${response.status})`);
    }

    return { key: payload.key, width: payload.width ?? null, height: payload.height ?? null };
}
