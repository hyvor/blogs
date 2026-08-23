import type { InternationalizationService } from '@hyvor/design/components';
import { getContext } from 'svelte';
import en from './locale/en.json';
import fr from './locale/fr.json';

type I18nType = InternationalizationService<typeof en>;

export function getMarketingI18n() {
	return getContext<I18nType>('i18n');
}

export const LANGUAGES_CONFIG = [
	{
		code: 'en',
		flag: '🇬🇧',
		name: 'English',
		strings: en,
		default: true
	},
	{
		code: 'fr',
		flag: '🇫🇷',
		name: 'Français',
		strings: fr
	}
];

export const DEFAULT_MARKETING_LANGUAGE = 'en';

export function buildMarketingUrl(path: string, currentLang: string, otherLang: string) {
	console.log(path, currentLang, otherLang);
	let basePath = path;

	// strip the current language prefix, if any, to get the language-agnostic path
	if (currentLang !== DEFAULT_MARKETING_LANGUAGE) {
		const prefix = `/${currentLang}`;
		if (basePath === prefix) {
			basePath = '/';
		} else if (basePath.startsWith(`${prefix}/`)) {
			basePath = basePath.slice(prefix.length);
		}
	}

	// the default language is served without a prefix
	if (otherLang === DEFAULT_MARKETING_LANGUAGE) {
		return basePath === '/' ? '' : basePath;
	}

	return basePath === '/' ? `/${otherLang}` : `/${otherLang}${basePath}`;
}
