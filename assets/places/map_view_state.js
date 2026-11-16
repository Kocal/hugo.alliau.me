export function parseMapViewHash(hash) {
    const params = new URLSearchParams(hash.replace(/^#/, ""));
    const zoom = Number(params.get("z") ?? NaN);
    const center = (params.get("center") ?? "").split(",").map(Number);

    if (!Number.isFinite(zoom) || center.length !== 2 || !center.every(Number.isFinite)) {
        return null;
    }

    return { zoom, center };
}

export function toMapViewState(zoom, { lat, lng }) {
    return { z: zoom, center: [lat.toFixed(5), lng.toFixed(5)] };
}

export function formatMapViewHash(state) {
    return `#${new URLSearchParams(state).toString()}`;
}
