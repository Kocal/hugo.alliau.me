const TILE_SIZE = 256;

// Below this zoom the world is shorter than the map and grey bands show above and below it
export function minZoomFillingHeight(height) {
    return Math.max(0, Math.ceil(Math.log2(height / TILE_SIZE)));
}
