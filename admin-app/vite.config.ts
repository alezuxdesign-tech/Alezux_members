import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

// https://vitejs.dev/config/
export default defineConfig({
  base: './',
  plugins: [
    react(),
    {
      name: 'wrap-iife',
      renderChunk(code, chunk) {
        if (chunk.fileName.endsWith('.js')) {
          return {
            code: `(() => {\n${code}\n})();`,
            map: null,
          };
        }
        return null;
      },
    },
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  build: {
    outDir: '../assets/dist',
    emptyOutDir: true,
    rollupOptions: {
      output: {
        entryFileNames: 'alezux-dashboard.js',
        chunkFileNames: 'chunks/[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            return 'alezux-dashboard.css';
          }
          return 'assets/[name][extname]';
        },
      },
    },
  },
});
