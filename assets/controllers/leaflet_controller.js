import {Controller} from "@hotwired/stimulus";
import L from 'leaflet';
import 'leaflet/dist/leaflet.min.css';

/* stimulusFetch: 'lazy' */
export default class  extends Controller {
  connect() {
    console.log("Leaflet controller connected");
  }

  #initMap(){
    this.map = L.map(this.element, {
      center: [51.505, -0.09],
      zoom: 2,
    });

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(this.map);

    L.marker([51.5, -0.09]).addTo(this.map);
    L.marker([45.5, -0.09]).addTo(this.map);
    L.marker([45.5, 12]).addTo(this.map);
  }
}