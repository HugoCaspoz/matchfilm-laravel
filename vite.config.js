import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'public/css/app.css',
                'public/js/app.js'
            ],
            refresh: true,
        }),
    ],
    // Configuración del servidor para forzar HTTPS en producción
    server: {
        https: process.env.APP_ENV === 'production',
    },
});
