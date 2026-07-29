import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/doctor.css',
                'resources/css/booking.css',
                'resources/js/app.js',
                'resources/js/doctor.js',
                'resources/js/booking.js',
            ],
            refresh: true,
        }),
    ],
});
