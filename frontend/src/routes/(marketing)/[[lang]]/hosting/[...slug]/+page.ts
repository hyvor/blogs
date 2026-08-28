import { loadDocsPage } from '@hyvor/design/marketing';
import { sections } from '../hosting';
import { DEFAULT_MARKETING_LANGUAGE } from '../../marketingLang';

export async function load({ params }: { params: { slug?: string; lang?: string } }) {
	const lang = params.lang && params.lang !== DEFAULT_MARKETING_LANGUAGE ? `/${params.lang}` : '';

	return loadDocsPage({
		basepath: `${lang}/hosting`,
		rootName: 'Hosting',
		sections,
		slug: params.slug ?? ''
	});
}
