import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue2'

const path = require("path");

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./"),
    },
    extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
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
        chunkFileNames: "findologic_ceres.chunk.js",
      }
    },
  },
})
