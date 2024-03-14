import { sentrySvelteKit } from "@sentry/sveltekit";
import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vitest/config';

export default defineConfig({
	plugins: [sentrySvelteKit({
        sourceMapsUploadOptions: {
            org: "hyvor",
            project: "hyvor-blogs-frontend"
        }
    }), sveltekit()],
	test: {
		include: ['src/**/*.{test,spec}.{js,ts}'],
		environment: 'happy-dom'
	},
	server: {
		port: 2210
	},
	envDir: '../',

	define: {
		// https://docs.excalidraw.com/docs/@excalidraw/excalidraw/integration#preact
		"process.env.IS_PREACT": JSON.stringify("true"),
	}
});