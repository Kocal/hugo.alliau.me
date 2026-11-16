import { Controller } from "@hotwired/stimulus";
import "../leaflet_global.js";
import { MarkerClusterGroup } from "leaflet.markercluster";
import "leaflet.markercluster/dist/MarkerCluster.css";
import { formatMapViewHash, parseMapViewHash, toMapViewState } from "../places/map_view_state.js";
import { markerIconHtml } from "../places/marker_icon.js";
import { minZoomFillingHeight } from "../places/min_zoom.js";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["map", "country"];
    static classes = ["cluster"];
    static values = { countries: Object };

    connect() {
        this.mapTarget.addEventListener("ux:map:pre-connect", this._onMapPreConnect);
        this.mapTarget.addEventListener("ux:map:connect", this._onMapConnect);
        this.mapTarget.addEventListener("ux:map:marker:before-create", this._onMarkerBeforeCreate);
    }

    disconnect() {
        this.mapTarget.removeEventListener("ux:map:pre-connect", this._onMapPreConnect);
        this.mapTarget.removeEventListener("ux:map:connect", this._onMapConnect);
        this.mapTarget.removeEventListener(
            "ux:map:marker:before-create",
            this._onMarkerBeforeCreate,
        );
    }

    _onMapPreConnect = (event) => {
        const { L } = event.detail;

        const view = parseMapViewHash(window.location.hash);

        if (view) {
            event.detail.zoom = view.zoom;
            event.detail.center = L.latLng(view.center[0], view.center[1]);
        } else {
            // Hacky, but allows to fit bounds to markers on initial load.
            // JavaScript side, because the page is behind HTTP cache
            this.mapTarget.setAttribute(
                "data-symfony--ux-leaflet-map--map-fit-bounds-to-markers-value",
                "true",
            );
        }

        event.detail.minZoom = minZoomFillingHeight(this.mapTarget.clientHeight);
        event.detail.bridgeOptions = {};
    };

    _onMapConnect = (event) => {
        const { map, L, markers } = event.detail;
        this.map = map;
        this.L = L;

        map.on("resize", () => map.setMinZoom(minZoomFillingHeight(map.getSize().y)));

        const clusters = new MarkerClusterGroup({
            showCoverageOnHover: false,
            iconCreateFunction: (cluster) =>
                L.divIcon({
                    html: `<div class="${this.clusterClasses.join(" ")}">${cluster.getChildCount()}</div>`,
                    iconSize: [36, 36],
                    className: "",
                }),
        });
        markers.forEach((marker) => marker.remove());
        clusters.addLayers(markers);
        map.addLayer(clusters);

        const updateState = () => {
            const state = toMapViewState(map.getZoom(), map.getCenter());

            window.history.replaceState(state, "", formatMapViewHash(state));
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
                html: markerIconHtml(definition.extra.icon_mask_uri),
                iconSize: [25, 41],
                iconAnchor: [12.5, 41],
                popupAnchor: [0, -41],
                className: "",
            }),
        };
    }

    onCountryChange(event) {
        if (!this.map || !this.L) return;

        const country = event.target.value;
        const coordinates = country
            ? (this.countriesValue[country]?.coordinates ?? [])
            : Object.values(this.countriesValue).flatMap((c) => c.coordinates);

        if (coordinates.length === 0) return;

        const bounds = this.L.latLngBounds(
            coordinates.map(([lat, lng]) => this.L.latLng(lat, lng)),
        );
        this.map.fitBounds(bounds, { padding: [40, 40] });
    }
}
