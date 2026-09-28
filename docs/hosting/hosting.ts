/**
 * Navigation of the Hyvor Blogs self-hosting docs (hyvor.com/blogs/hosting).
 *
 * This directory is synced to hyvor/core, which renders the docs.
 * Keep it self-contained: only import from within this directory,
 * `svelte`, and `@hyvor/design`.
 */
import type { NavSectionConfig } from '@hyvor/design/marketing';
import type { Component } from 'svelte';
import en from './locale/en.json';
import fr from './locale/fr.json';

export const LANGUAGES = ['en', 'fr'];

const STRINGS: Record<string, typeof en> = { en, fr };

const PAGES = import.meta.glob<{ default: Component }>('./*/*.md');

// loads a page in the given language, falling back to English if it is not translated yet
async function loadPage(lang: string, file: string): Promise<Component> {
	const loader = PAGES[`./${lang}/${file}.md`] ?? PAGES[`./en/${file}.md`];
	if (!loader) {
		throw new Error(`Hosting page not found: ${file}`);
	}
	return (await loader()).default;
}

export async function getSections(lang: string): Promise<NavSectionConfig[]> {
	const s = STRINGS[lang] ?? en;
	const getComponent = (file: string) => loadPage(lang, file);

	return [
		{
			name: '',
			navs: [
				{
					type: 'page',
					slug: '',
					name: s.pages.introduction,
					content: await getComponent('Introduction')
				},
				{
					type: 'page',
					slug: 'deploy',
					name: s.pages.deploy,
					content: await getComponent('Deploy')
				},
				{
					type: 'page',
					slug: 'reverse-proxy',
					name: s.pages.reverseProxy,
					content: await getComponent('ReverseProxy')
				}
			]
		},
		{
			name: s.sections.configuration,
			navs: [
				{
					type: 'page',
					slug: 'env',
					name: s.pages.env,
					content: await getComponent('Env')
				},
				{
					type: 'page',
					slug: 'delivery-domain',
					name: s.pages.deliveryDomain,
					content: await getComponent('DeliveryDomain')
				}
			]
		}
	];
}
