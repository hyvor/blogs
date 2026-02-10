import consoleApi from '../consoleApi';
import type { UnfoldedEmbed, UnfoldedLink } from '../types';

export function getUnfold<T extends 'embed' | 'link'>(url: string, type: T) {
	return consoleApi.get<T extends 'embed' ? UnfoldedEmbed : UnfoldedLink>({
		endpoint: '/url-data',
		data: {
			url,
			type
		}
	});
}
