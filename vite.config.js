import { createVuePlugin as vue } from 'vite-plugin-vue2';
import { defineConfig } from 'vite';
import mkcert from 'vite-plugin-mkcert';
import commonjs from '@rollup/plugin-commonjs';

const path = require('path');

export default defineConfig({
    server: { https: true },
    plugins: [vue(), mkcert(), commonjs()],
    alias: {
        '@': path.resolve(__dirname, './'),
    },
    build: {
        lib: {
            entry: './resources/js/src/index.ts',
            formats: ['cjs']
        },
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
        commonjsOptions: {
            transformMixedEsModules: true
        }

    },
});
