import { createVuePlugin as vue } from "vite-plugin-vue2";
import { defineConfig } from 'vite';
const path = require("path");

export default defineConfig({
    plugins: [vue()],
    alias: {
        "@": path.resolve(__dirname, "./"),
    },
    build: {
        rollupOptions: {
            input: {
                app: './resources/js/src/index.ts',
            },
            output: {
                dir: './resources/js/dist/',
                entryFileNames: 'findologic_ceres.js',
                assetFileNames: 'findologic_ceres.css',
            }
        },
    },
})