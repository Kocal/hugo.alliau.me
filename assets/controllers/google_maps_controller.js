import {Controller} from "@hotwired/stimulus";

/* stimulusFetch: "lazy" */
export default class extends Controller
{
    static values = {
        places: Array,
    }

    connect() {
        console.log(this.element);
        console.log(this.placesValue);

        this.#initMap();
    }

    async #initMap() {
        const {Map} = await google.maps.importLibrary("maps")
        const {AdvancedMarkerElement} = await google.maps.importLibrary("marker")

        const map = new Map(this.element, {
            center: new google.maps.LatLng(0, 0),
            zoom: 2,
            mapId: '2b2d73ba4b8c7b41',
        });

        const markers = this.placesValue.map(place => {
            return new AdvancedMarkerElement({
                map,
                position:  new google.maps.LatLng(place.address.coordinates[0], place.address.coordinates[1]),
            })
        });

        const bounds = new google.maps.LatLngBounds();
        markers.forEach(marker => bounds.extend(marker.position));
        map.fitBounds(bounds);
    }
}