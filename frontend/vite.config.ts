import { sveltekit } from '@sveltejs/kit/vite';
import type { PluginOption } from 'vite';
import { defineConfig } from 'vitest/config';

function watchHds(): PluginOption {
	return {
		name: 'watch-hds',
		config() {
			return {
				server: {
					watch: {
						ignored: [
							(path) => {
								return (
									path.includes('node_modules') && !path.includes('@hyvor/design')
								);
							}
						]
					}
				},
				optimizeDeps: {
					exclude: ['@hyvor/design']
				}
			};
		}
	};
}

export default defineConfig({
	plugins: [sveltekit(), watchHds()],

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
