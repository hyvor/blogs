import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel([
            // console
            'resources/js/console/console.tsx',

            // landing
            'resources/css/landing/landing.scss'
        ]),
        react(),
    ],
});