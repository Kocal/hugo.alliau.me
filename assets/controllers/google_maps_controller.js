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
        const {AdvancedMarkerElement, PinElement} = await google.maps.importLibrary("marker")

        const map = new Map(this.element, {
            center: new google.maps.LatLng(0, 0),
            zoom: 2,
            mapId: '2b2d73ba4b8c7b41',
        });

        const infoWindows = [];

        const markers = this.placesValue.map(place => {
            const position = new google.maps.LatLng(place.address.coordinates[0], place.address.coordinates[1]);

            const pinElement = new PinElement({
                // background: place.iconBackgroundColor,
                glyph: new URL(String(place.iconMaskUri)),
                glyphColor: 'white',
            });

            const marker = new AdvancedMarkerElement({
                map,
                position,
                title: place.address.name,
                content: pinElement.element,
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `
<div style="max-width: 320px;">
    <p class="font-bold text-xl">${place.address.name}</p>
    <p class="text-base font-normal">${place.address.formattedAddress}</p>
    <ul class="flex flex-wrap gap-1 mt-1">
        ${place.types.map(type => `<li class="text-primary-50 bg-primary-700 uppercase text-xs font-bold p-0.5 rounded">${type}</li>`).join('')}
    </ul>
    <p class="text-base mt-2">
        <a href="${place.googleMapsUrl}" rel="noreferrer noopener" class="underline font-normal text-primary-600">
            Google Maps
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline-block w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
        </a>
    </p>
</div>
`,
                ariaLabel: "Uluru",
            });

            marker.addListener("click", () => {
                infoWindows.forEach(infoWindow => infoWindow.close());
                infoWindow.open({
                    anchor: marker,
                    map,
                });
            });

            infoWindows.push(infoWindow);

            return marker;
        });

        const bounds = new google.maps.LatLngBounds();
        markers.forEach(marker => bounds.extend(marker.position));
        map.fitBounds(bounds);
    }
}