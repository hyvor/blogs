import { DEFAULT_MARKETING_LANGUAGE, LANGUAGES_CONFIG } from './marketingLang';
import { error } from '@sveltejs/kit';

export function load({ params }: { params: { lang: string } }) {
	const lang = params.lang || DEFAULT_MARKETING_LANGUAGE;
	const languageConfig = LANGUAGES_CONFIG.find((config) => config.code === lang);

	if (!languageConfig) {
		error(404, `Language '${lang}' not found.`);
	}

	return {
		lang: languageConfig.code,
		languageCodes: LANGUAGES_CONFIG.map((config) => config.code)
	};
}
