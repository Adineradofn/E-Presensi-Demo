import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    tailwindcss(),
  ],
  server: {
    host: '0.0.0.0',   // dengarkan semua interface supaya bisa diakses dari LAN
    port: 5173,
    hmr: {
      host: '192.168.1.2', // ganti dengan IP laptopmu (dari ipconfig)
      protocol: 'ws',
      port: 5173,
    },
  },
});
