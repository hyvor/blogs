import { get } from 'svelte/store';
import { postVariantStore } from '../../../postStore';

export interface PublishIssues {
	// errors block publishing
	titleError: string | null;
	// warnings do not block publishing
	slugWarning: string | null;
	descriptionWarning: string | null;
	hasErrors: boolean;
}

export const emptyPublishIssues: PublishIssues = {
	titleError: null,
	slugWarning: null,
	descriptionWarning: null,
	hasErrors: false
};

export function getPublishIssues(): PublishIssues {
	const variant = get(postVariantStore);

	const titleError = !variant?.title?.trim() ? 'Title is empty.' : null;
	const slugWarning = !variant?.slug?.trim()
		? 'Slug is empty. It will be generated from the title.'
		: null;
	const descriptionWarning = !variant?.description?.trim() ? 'Description is empty.' : null;

	return {
		titleError,
		slugWarning,
		descriptionWarning,
		hasErrors: titleError !== null
	};
}
