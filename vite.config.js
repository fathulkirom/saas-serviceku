import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['favicon.ico', 'favicon.svg', 'icon-192.png', 'icon-512.png'],
            manifest: {
                name: 'ServiceKU - Manajemen Servis Center',
                short_name: 'ServiceKU',
                description: 'Aplikasi manajemen pusat servis elektronik & handphone',
                theme_color: '#4F46E5',
                background_color: '#ffffff',
                display: 'standalone',
                orientation: 'portrait',
                scope: '/',
                start_url: '/',
                id: '/',
                lang: 'id-ID',
                dir: 'ltr',
                categories: ['business', 'utilities'],
                display_override: ['standalone', 'minimal-ui'],
                icons: [
                    { src: '/icon-192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icon-512.png', sizes: '512x512', type: 'image/png', purpose: 'any' },
                    { src: '/icon-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
                screenshots: [],
                shortcuts: [
                    {
                        name: 'Dashboard',
                        short_name: 'Dashboard',
                        description: 'Buka dashboard',
                        url: '/dashboard',
                        icons: [{ src: '/icon-192.png', sizes: '192x192' }],
                    },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
                runtimeCaching: [
                    {
                        urlPattern: /^https?:\/\/.*\/api\/.*/i,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            expiration: { maxEntries: 50, maxAgeSeconds: 300 },
                        },
                    },
                    {
                        urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp)$/,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'image-cache',
                            expiration: { maxEntries: 100, maxAgeSeconds: 86400 },
                        },
                    },
                ],
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: 'localhost',
            protocol: 'ws',
        },
    },
});
