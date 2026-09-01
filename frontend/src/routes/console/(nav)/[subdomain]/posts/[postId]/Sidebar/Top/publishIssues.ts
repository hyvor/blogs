import { get } from 'svelte/store';
import { postVariantStore } from '../../../postStore';

// i18n keys, resolved by the caller
type PublishIssueKey =
	| 'console.postEditor.publish.issues.titleEmpty'
	| 'console.postEditor.publish.issues.slugEmpty'
	| 'console.postEditor.publish.issues.descriptionEmpty';

export interface PublishIssues {
	// errors block publishing
	titleError: PublishIssueKey | null;
	// warnings do not block publishing
	slugWarning: PublishIssueKey | null;
	descriptionWarning: PublishIssueKey | null;
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

	const titleError = !variant?.title?.trim()
		? 'console.postEditor.publish.issues.titleEmpty'
		: null;
	const slugWarning = !variant?.slug?.trim() ? 'console.postEditor.publish.issues.slugEmpty' : null;
	const descriptionWarning = !variant?.description?.trim()
		? 'console.postEditor.publish.issues.descriptionEmpty'
		: null;

	return {
		titleError,
		slugWarning,
		descriptionWarning,
		hasErrors: titleError !== null
	};
}
