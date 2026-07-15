import { defineConfig } from "vite";
import Symfony from "@symfony/reprise/vite";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.js",
                admin: "./assets/admin.js",
            },
        },
    },
    plugins: [
        tailwindcss(),
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
