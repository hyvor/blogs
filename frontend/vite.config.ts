import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vitest/config';

export default defineConfig({
	plugins: [sveltekit()],

	test: {
		include: ['src/**/*.{test,spec}.{js,ts}'],
		environment: 'happy-dom'
	},
	server: {
		port: 36201,
		host: '0.0.0.0',
		allowedHosts: true,
		fs: {
			strict: false
		}
	},

	envDir: '../',

	define: {
		// https://docs.excalidraw.com/docs/@excalidraw/excalidraw/integration#preact
		'process.env.IS_PREACT': JSON.stringify('true')
	}
});
