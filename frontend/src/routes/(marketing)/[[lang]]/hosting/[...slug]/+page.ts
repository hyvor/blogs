import { loadDocsPage } from '@hyvor/design/marketing';
import { getSections } from '$docs/hosting/hosting';
import { DEFAULT_MARKETING_LANGUAGE } from '../../marketingLang';

export async function load({ params }: { params: { slug?: string; lang?: string } }) {
	const lang = params.lang ?? DEFAULT_MARKETING_LANGUAGE;
	const prefix = lang !== DEFAULT_MARKETING_LANGUAGE ? `/${lang}` : '';

	return loadDocsPage({
		basepath: `${prefix}/hosting`,
		rootName: 'Hosting',
		sections: await getSections(lang),
		slug: params.slug ?? ''
	});
}
