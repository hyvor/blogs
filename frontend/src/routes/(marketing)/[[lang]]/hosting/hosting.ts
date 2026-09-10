import type { NavSectionConfig } from '@hyvor/design/marketing';
import Deploy from './content/Deploy.md';
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
					name: t('hosting.pages.introduction'),
					content: await getComponent('Introduction')
				},
				{
					type: 'page',
					slug: 'deploy',
					name: t('hosting.pages.deploy'),
					content: Deploy
				},
				{
					type: 'page',
					slug: 'reverse-proxy',
					name: t('hosting.pages.reverseProxy'),
					content: await getComponent('ReverseProxy')
				}
			]
		},
		{
			name: t('hosting.sections.configuration'),
			navs: [
				{
					type: 'page',
					slug: 'env',
					name: t('hosting.pages.env'),
					content: await getComponent('Env')
				},
				{
					type: 'page',
					slug: 'delivery-domain',
					name: t('hosting.pages.deliveryDomain'),
					content: await getComponent('DeliveryDomain')
				}
			]
		}
	];
}
