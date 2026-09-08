import type { NavSectionConfig } from '@hyvor/design/marketing';
import Introduction from './content/Introduction.md';
import Deploy from './content/Deploy.md';
import Env from './content/Env.md';
import DeliveryDomain from './content/DeliveryDomain.md';
import type { Component } from 'svelte';
import { buildI18n, DEFAULT_MARKETING_LANGUAGE } from '../marketingLang';

export async function getSections(lang: string): Promise<NavSectionConfig[]> {
	async function getComponent(path: string): Promise<Component> {
		const folder = lang ? lang.replace('/', '') : 'content';
		return (await import(`./${folder}/${path}.md`)).default;
	}

	const langCode = lang ? lang.replace('/', '') : DEFAULT_MARKETING_LANGUAGE;
	const i18n = buildI18n(langCode);
	const t = i18n.t.bind(i18n);

	return [
		{
			name: '',
			navs: [
				{
					type: 'page',
					slug: '',
					name: 'Introduction',
					content: await getComponent('Introduction')
				},
				{
					type: 'page',
					slug: 'deploy',
					name: 'Deploy',
					content: Deploy
				},
				{
					type: 'page',
					slug: 'reverse-proxy',
					name: 'Reverse Proxy',
					content: await getComponent('ReverseProxy')
				}
			]
		},
		{
			name: 'Configuration',
			navs: [
				{
					type: 'page',
					slug: 'env',
					name: 'Environment Variables',
					content: await getComponent('Env')
				},
				{
					type: 'page',
					slug: 'delivery-domain',
					name: 'Delivery Domain',
					content: await getComponent('DeliveryDomain')
				}
			]
		}
	];
}
