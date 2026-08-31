import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
	plugins: [sveltekit()],
	server: {
		port: 36201,
		host: '0.0.0.0',
		allowedHosts: true,
		fs: {
			strict: false
		}
	},

	envDir: '../',

	// @ts-ignore
	test: {
		include: ['src/**/*.{test,spec}.{js,ts}'],
		environment: 'happy-dom'
	}
});
