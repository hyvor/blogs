import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';
import adapter from '@sveltejs/adapter-static';
import { APP_REDIRECTS } from './src/redirects.js';
import { markdownPlugin } from '@hyvor/design/dev';

/** @type {import('@sveltejs/kit').Config} */
const config = {
	extensions: ['.svelte', '.md'],

	preprocess: [markdownPlugin(), vitePreprocess()],

	kit: {
		adapter: adapter({
			fallback: 'fallback.html'
		}),
		prerender: {
			handleMissingId: 'warn',
			handleHttpError: 'warn',
			entries: ['*', '/fr', ...Object.keys(APP_REDIRECTS)]
		}
	},

	compilerOptions: {
		experimental: {
			async: true
		}
	}
};

export default config;
