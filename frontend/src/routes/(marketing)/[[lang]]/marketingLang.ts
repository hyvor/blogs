import { InternationalizationService } from '@hyvor/design/components';
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

export function buildI18n(lang: string) {
	return new InternationalizationService(
		LANGUAGES_CONFIG,
		lang
	);
}

export const DEFAULT_MARKETING_LANGUAGE = 'en';

// Header/Footer render outside [[lang]]/+layout.svelte's InternationalizationProvider
// (they're rendered by the outer (marketing)/+layout.svelte, as siblings to it - see
// that file - not as its descendants), so they can't read the "i18n" context via
// getMarketingI18n(). They already derive the current language from the URL directly,
// so this looks a string up in that language's own strings object instead, falling
// back to the default language for any key that isn't translated yet.
export function getStaticString(strings: Record<string, any>, key: string): string {
	const dig = (obj: Record<string, any>) =>
		key.split('.').reduce<any>((o, k) => (o && typeof o === 'object' ? o[k] : undefined), obj);
	return dig(strings) ?? dig(en) ?? '';
}

export function buildMarketingUrl(path: string, currentLang: string, otherLang: string) {
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
