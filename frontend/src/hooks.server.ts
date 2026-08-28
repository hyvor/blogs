import type { Handle } from '@sveltejs/kit';

export const handle: Handle = ({ event, resolve }) => {
	function getLanguage(): string {
		if (event.params.lang) {
			return event.params.lang;
		}
		return 'en';
	}
	return resolve(event, {
		transformPageChunk: ({ html }) => html.replace('%lang%', getLanguage())
	});
};
