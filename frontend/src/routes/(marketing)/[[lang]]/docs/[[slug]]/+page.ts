import { loadDocsPage } from '@hyvor/design/marketing';
import { getSections } from '$docs/docs/docs';
import { DEFAULT_MARKETING_LANGUAGE } from '../../marketingLang';

export async function load({ params }) {
	const lang = params.lang ?? DEFAULT_MARKETING_LANGUAGE;
	const prefix = lang !== DEFAULT_MARKETING_LANGUAGE ? `/${lang}` : '';

	return loadDocsPage({
		basepath: `${prefix}/docs`,
		rootName: 'Docs',
		sections: await getSections(lang),
		slug: params.slug ?? ''
	});
}
