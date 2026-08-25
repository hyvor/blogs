import { integrations } from '../integrations';
import { error } from '@sveltejs/kit';

export async function load({ params }) {
	const slug = params.slug;
	const integration = integrations.find((i) => i.slug === slug);

	if (!integration) {
		error(404, 'Not found');
	}

	return integration;
}
