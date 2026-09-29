import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';
import adapter from '@sveltejs/adapter-static';
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
			entries: ['*']
		},
		inlineStyleThreshold: 2048,
		alias: {
			// docs are kept in the repo root, and synced to hyvor/core
			$docs: '../docs'
		},
		typescript: {
			config(config) {
				// ../docs has no node_modules, resolve its types from here
				config.compilerOptions.paths['@hyvor/design/marketing'] = [
					'../node_modules/@hyvor/design/dist/marketing/index.d.ts'
				];
				config.include.push('../../docs/**/*.ts');
			}
		}
	},

	compilerOptions: {
		experimental: {
			async: true
		}
	}
};

export default config;
