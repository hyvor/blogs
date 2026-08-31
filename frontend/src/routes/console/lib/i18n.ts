import type { InternationalizationService } from '@hyvor/design/components';
import { getContext } from 'svelte';
import en from '../../../../../shared/locale/en.json';
import fr from '../../../../../shared/locale/fr.json';

type I18nType = InternationalizationService<typeof en>;

export function getI18n() {
	return getContext<I18nType>('i18n');
}

export const CONSOLE_LANGUAGES = [
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
