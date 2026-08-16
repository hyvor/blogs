import { get } from 'svelte/store';
import consoleApi from '../../../lib/consoleApi';
import type {
	PostSuggestionAuthor,
	PostSuggestionReply,
	PostSuggestionSourceEntry
} from '../../../lib/types';
import { postStore, postVariantLanguageStore } from './postStore';

// Syncs @hyvor/richtext's suggestionsPlugin `source` (get/create/reply/resolve) and
// `resolveAuthor` to the backend - see postId/post/[postId]/Body/Editor/suggestions.ts,
// where these are wired into the plugin's config.

function postId() {
	return get(postStore).id;
}

function languageId() {
	return get(postVariantLanguageStore).id;
}

export function getPostSuggestions(ids: string[]) {
	return consoleApi.post<Record<string, PostSuggestionSourceEntry>>({
		endpoint: `/post/${postId()}/variant/suggestions/get`,
		data: { language_id: languageId(), ids }
	});
}

export function createPostSuggestion(id: string, type: string) {
	return consoleApi.post<PostSuggestionSourceEntry>({
		endpoint: `/post/${postId()}/variant/suggestions`,
		data: { language_id: languageId(), id, type }
	});
}

export function replyToPostSuggestion(
	suggestionId: string,
	replyId: string,
	content: string,
	type?: string
) {
	return consoleApi.post<PostSuggestionReply>({
		endpoint: `/post/${postId()}/variant/suggestions/${suggestionId}/replies`,
		data: { language_id: languageId(), id: replyId, content, type }
	});
}

export function resolvePostSuggestion(
	suggestionId: string,
	decision: 'accept' | 'reject' | 'resolve'
) {
	return consoleApi.post<PostSuggestionSourceEntry>({
		endpoint: `/post/${postId()}/variant/suggestions/${suggestionId}/resolve`,
		data: { language_id: languageId(), decision }
	});
}

export function resolveSuggestionAuthor(hyvorUserId: number) {
	return consoleApi.get<PostSuggestionAuthor>({
		endpoint: '/users/resolve-author',
		data: { hyvor_user_id: hyvorUserId }
	});
}
