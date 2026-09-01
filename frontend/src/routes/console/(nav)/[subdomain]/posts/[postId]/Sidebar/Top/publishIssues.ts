import { get } from 'svelte/store';
import { documentStore, postVariantStore } from '../../../postStore';
import { hasPendingSuggestions } from '../../../../../../lib/prosemirror/suggestions';

// i18n keys, resolved by the caller
type PublishIssueKey =
	| 'console.postEditor.publish.issues.titleEmpty'
	| 'console.postEditor.publish.issues.slugEmpty'
	| 'console.postEditor.publish.issues.descriptionEmpty'
	| 'console.postEditor.publish.issues.pendingSuggestions';

export interface PublishIssues {
	// errors block publishing
	titleError: PublishIssueKey | null;
	suggestionsError: PublishIssueKey | null;
	// warnings do not block publishing
	slugWarning: PublishIssueKey | null;
	descriptionWarning: PublishIssueKey | null;
	hasErrors: boolean;
}

export const emptyPublishIssues: PublishIssues = {
	titleError: null,
	suggestionsError: null,
	slugWarning: null,
	descriptionWarning: null,
	hasErrors: false
};

export function getPublishIssues(): PublishIssues {
	const variant = get(postVariantStore);
	const document = get(documentStore);

	const titleError = !variant?.title?.trim()
		? 'console.postEditor.publish.issues.titleEmpty'
		: null;
	const slugWarning = !variant?.slug?.trim() ? 'console.postEditor.publish.issues.slugEmpty' : null;
	const descriptionWarning = !variant?.description?.trim()
		? 'console.postEditor.publish.issues.descriptionEmpty'
		: null;
	const suggestionsError = hasPendingSuggestions(document?.checkpoint_content ?? null)
		? 'console.postEditor.publish.issues.pendingSuggestions'
		: null;

	return {
		titleError,
		suggestionsError,
		slugWarning,
		descriptionWarning,
		hasErrors: titleError !== null || suggestionsError !== null
	};
}
