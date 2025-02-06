import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

/* if you're using React */
// import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        symfonyPlugin({
            stimulus: true,
        }),
    ],
    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.js"
            },
        }
    },
    resolve: {
        alias: {
            'leaflet/dist/leaflet.min.css': 'leaflet/dist/leaflet.css',
        }
    }
});
