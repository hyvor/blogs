import { loadDocsPage } from '@hyvor/design/marketing';
import { sections } from '../hosting';

export async function load({ params }: { params: { slug?: string } }) {
	return loadDocsPage({
		basepath: '/hosting',
		rootName: 'Hosting',
		sections,
		slug: params.slug ?? ''
	});
}
