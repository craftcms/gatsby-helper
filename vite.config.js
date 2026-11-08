import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  build: {
    emptyOutDir: true,
    rollupOptions: {
      // Supplied by the control panel's import map
      external: ['@craftcms/cp'],
    },
  },
  plugins: [
    laravel({
      input: ['resources/js/live-preview.js'],
      publicDirectory: 'public',
      buildDirectory: 'build',
      hotFile: 'public/hot',
      refresh: false,
    }),
  ],
});
