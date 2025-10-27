import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        this.element.addEventListener("ux:map:pre-connect", this._onMapPreConnect);
        this.element.addEventListener("ux:map:connect", this._onMapConnect);
        this.element.addEventListener("ux:map:marker:before-create", this._onMarkerBeforeCreate);
    }

    disconnect() {
        this.element.removeEventListener("ux:map:pre-connect", this._onMapPreConnect);
        this.element.removeEventListener("ux:map:connect", this._onMapConnect);
        this.element.removeEventListener("ux:map:marker:before-create", this._onMarkerBeforeCreate);
    }

    _onMapPreConnect = (event) => {
        const { L } = event.detail;

        if (window.location.hash) {
            try {
                const state = Object.fromEntries(new URLSearchParams(window.location.hash.slice(1)));
                const zoom = Number(state.z);
                const center = state.center.split(",").map(Number);

                event.detail.zoom = zoom;
                event.detail.center = L.latLng(center[0], center[1]);
            } catch (e) {
                console.error("Invalid state in URL hash:", e);
            }
        } else {
            // Hacky, but allows to fit bounds to markers on initial load.
            // JavaScript side, because the page is behind HTTP cache
            this.element.setAttribute('data-symfony--ux-leaflet-map--map-fit-bounds-to-markers-value', 'true');
        }

        event.detail.bridgeOptions = {};
    };

    _onMapConnect = (event) => {
        const { map } = event.detail;

        const updateState = () => {
            const center = map.getCenter();
            const zoom = map.getZoom();

            const state = {
                z: zoom,
                center: [center.lat.toFixed(5), center.lng.toFixed(5)],
            };

            window.history.replaceState(state, "", `#${new URLSearchParams(state).toString()}`);
        };

        // Event "load" does not work here, so we use a timeout as a workaround
        setTimeout(() => {
            map.addEventListener("zoom", () => updateState());
            map.addEventListener("move", () => updateState());
        }, 1000);
    };

    _onMarkerBeforeCreate(event) {
        const { L, definition } = event.detail;

        definition.bridgeOptions = {
            icon: L.divIcon({
                html: `
<div class="relative">
    <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill-rule="evenodd" stroke-linecap="round" clip-rule="evenodd" viewBox="0 0 500 820">
        <defs>
           <linearGradient id="__sf_ux_map_gradient_marker_fill" x1="0" x2="1" y1="0" y2="0" gradientTransform="matrix(0 -37.57 37.57 0 416.45 541)" gradientUnits="userSpaceOnUse">
               <stop offset="0" stop-color="#126FC6" />
               <stop offset="1" stop-color="#4C9CD1" />
           </linearGradient>
           <linearGradient id="__sf_ux_map_gradient_marker_border" x1="0" x2="1" y1="0" y2="0" gradientTransform="matrix(0 -19.05 19.05 0 414.48 522.49)" gradientUnits="userSpaceOnUse">
               <stop offset="0" stop-color="#2E6C97" />
               <stop offset="1" stop-color="#3883B7" />
           </linearGradient>
        </defs>
        <path fill="url(#__sf_ux_map_gradient_marker_fill)" stroke="url(#__sf_ux_map_gradient_marker_border)" stroke-width="1.1" d="M416.54 503.61c-6.57 0-12.04 5.7-12.04 11.87 0 2.78 1.56 6.3 2.7 8.74l9.3 17.88 9.26-17.88c1.13-2.43 2.74-5.79 2.74-8.74 0-6.18-5.38-11.87-11.96-11.87Z" transform="translate(-7889.1 -9807.44) scale(19.54)"/>
    </svg>

    <img src="${definition.extra.icon_mask_uri}" alt="" class="absolute" style="width: 12px; top: 8px; transform: translate3d(50%, 0, 0); filter: invert(1)" />
</div>
`,
                iconSize: [25, 41],
                iconAnchor: [12.5, 41],
                popupAnchor: [0, -41],
                className: "",
            }),
        };
    }
}
