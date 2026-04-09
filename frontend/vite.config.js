import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: '../backend/public/hot',
            publicDirectory: '../backend/public',
            buildDirectory: 'build',
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/motor-flujos.jsx',
                'resources/js/dashboard-motor.jsx',
                'resources/js/dashboard-builder.jsx',
            ],
            refresh: [
                '../backend/App/**',
                '../backend/config/**',
                '../backend/routes/**',
                '../backend/resources/views/**',
            ],
        }),
        react(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: false,
        hmr: {
            host: 'localhost',
        },
    },
});
