import * as L from "leaflet";

// leaflet.markercluster registers itself on a global, extensible `L`, which Leaflet's ESM build does not provide
window.L = { ...L };
