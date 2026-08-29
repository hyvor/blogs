import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';
import adapter from '@sveltejs/adapter-static';
import { APP_REDIRECTS } from './src/redirects.js';
import { markdownPlugin } from '@hyvor/design/dev';

/** @type {import('@sveltejs/kit').Config} */
const config = {
	extensions: ['.svelte', '.md'],

	preprocess: [markdownPlugin(), vitePreprocess()],

	kit: {
		// adapter-auto only supports some environments, see https://kit.svelte.dev/docs/adapter-auto for a list.
		// If your environment is not supported or you settled on a specific environment, switch out the adapter.
		// See https://kit.svelte.dev/docs/adapters for more information about adapters.
		adapter: adapter({
			fallback: 'fallback.html'
		}),
		prerender: {
			/* TODO: REMOVE THIS! */
			handleMissingId: 'ignore',
			handleHttpError: 'ignore',
			// TODO: remove after toggle is added
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
