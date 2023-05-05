import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        (laravel as any).default([
            // console
            'resources/js/console/console.tsx',

            // config
            'resources/js/configdef/demo/configdef-demo.tsx',

            // iframe
            'resources/js/embed/iframe.ts',

            // landing
            'resources/css/landing/landing.scss'
        ]),
        react()
    ]
});