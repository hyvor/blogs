import { loadDocsPage } from '@hyvor/design/marketing';
import { sections } from './docs';
import { DEFAULT_MARKETING_LANGUAGE } from '../../marketingLang';

export async function load({ params }) {
	const lang = params.lang && params.lang !== DEFAULT_MARKETING_LANGUAGE ? `/${params.lang}` : '';

	return loadDocsPage({
		basepath: `${lang}/docs`,
		rootName: 'Docs',
		sections,
		slug: params.slug ?? ''
	});
}
