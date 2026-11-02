import { defineConfig } from "vite";
import Symfony from "@symfony/reprise/vite";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    input: {
        app: "./assets/app.js",
        admin: "./assets/admin.js",
    },
    plugins: [
        vue(),
        Symfony({
            stimulus: "./assets/controllers.json",
            copy: [{ from: "./assets/images/", to: "images/" }],
        }),
    ],
    resolve: {
        alias: {
            "leaflet/dist/leaflet.min.css": "leaflet/dist/leaflet.css",
        },
    },
});
