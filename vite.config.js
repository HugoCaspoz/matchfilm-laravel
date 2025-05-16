import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    // Configuración del servidor para forzar HTTPS en producción
    server: {
        https: process.env.APP_ENV === 'production',
    },
});