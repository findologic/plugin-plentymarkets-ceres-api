import { createVuePlugin as vue } from 'vite-plugin-vue2';
import { defineConfig } from 'vite';
import mkcert from 'vite-plugin-mkcert';

const path = require('path');

export default defineConfig({
    server: { https: true },
    plugins: [vue(), mkcert()],
    alias: {
        '@': path.resolve(__dirname, './'),
    },
    build: {
        target: 'es2020',
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
});
