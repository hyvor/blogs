import { get } from 'svelte/store';
import type { Post, PostVariant, PostListItem, User, Tag } from '../../../lib/types';
import consoleApi from '../../../lib/consoleApi';
import {
	postStore,
	postVariantLanguageStore,
	updatePostStore,
	updatePostVariantStore
} from './postStore';

// API

interface GetPostsData {
	status?: 'featured' | 'published' | 'draft' | 'scheduled';
	author_id?: number;
	tag_id?: number;
	start_timestamp?: number; // unix timestamp
	end_timestamp?: number; // unix timestamp
	search?: string;
	language_id?: number;
	limit?: number; // default 50, max 100
	offset?: number;
}

export function getPosts(data: GetPostsData) {
	return consoleApi.get<PostListItem[]>({
		endpoint: '/posts',
		data
	});
}

export function getPages() {
	return consoleApi.get<PostListItem[]>({
		endpoint: '/pages'
	});
}

export function getPost(id: number, variantLanguageCode: string | null = null) {
	return consoleApi.get<{
		post: Post;
		variant: PostVariant | null;
	}>({
		endpoint: `/post/${id}`,
		data: variantLanguageCode ? { variant_language_code: variantLanguageCode } : undefined
	});
}

export function createPost(isPage = false) {
	return consoleApi.post<Post>({
		endpoint: '/post',
		data: {
			is_page: isPage
		}
	});
}

export function updatePost(data: Partial<Post>, updateStore = true) {
	const promise = consoleApi.patch<Post>({
		endpoint: `/post/${get(postStore).id}`,
		data
	});

	promise.then((res) => {
		if (updateStore) {
			// update only the fields that were changed
			const update = {} as Partial<Post>;
			Object.keys(data).forEach((key) => ((update as any)[key] = (res as any)[key]));
			updatePostStore(update, true);
		}
	});

	return promise;
}

export function deletePost() {
	return consoleApi.delete({
		endpoint: `/post/${get(postStore).id}`
	});
}

export function deletePostById(postId: number) {
	return consoleApi.delete({
		endpoint: `/post/${postId}`
	});
}

export function updatePostAuthors(authors: User[], updateStore = true) {
	const postId = get(postStore).id;

	const promise = consoleApi.patch({
		endpoint: `/post/${postId}/authors`,
		data: {
			ids: authors.map((author) => author.id)
		}
	});

	promise.then(() => {
		if (updateStore) {
			updatePostStore({ authors }, true);
		}
	});

	return promise;
}

export function updatePostTags(tags: Tag[], updateStore = true) {
	const postId = get(postStore).id;

	const promise = consoleApi.patch({
		endpoint: `/post/${postId}/tags`,
		data: {
			ids: tags.map((tag) => tag.id)
		}
	});

	promise.then(() => {
		if (updateStore) {
			updatePostStore({ tags }, true);
		}
	});

	return promise;
}

// updates current post variant
export function updatePostVariant(
	data: Partial<PostVariant> & { redirect_on_slug_change?: boolean },
	updateStore = true,
	additionalKeysToUpdate: (keyof PostVariant)[] = []
) {
	const postId = get(postStore).id;
	const languageId = get(postVariantLanguageStore).id;
	data.language_id = languageId;

	const promise = consoleApi.patch<PostVariant>({
		endpoint: `/post/${postId}/variant`,
		data
	});

	promise.then((res) => {
		if (updateStore) {
			// update only the fields that were changed
			const update = {} as Partial<PostVariant>;
			Object.keys(data).forEach((key) => ((update as any)[key] = (res as any)[key]));
			additionalKeysToUpdate.forEach((key) => {
				(update as any)[key] = (res as any)[key];
			});
			updatePostVariantStore(update, true);
		}
	});

	return promise;
}

export function publishPostVariant(updateStore = true) {
	const postId = get(postStore).id;
	const languageId = get(postVariantLanguageStore).id;

	const promise = consoleApi.post<PostVariant>({
		endpoint: `/post/${postId}/variant/publish`,
		data: { language_id: languageId }
	});

	promise.then((res) => {
		if (updateStore) {
			updatePostVariantStore(res, true);
		}
	});

	return promise;
}

export function unpublishPostVariant(updateStore = true) {
	const postId = get(postStore).id;
	const languageId = get(postVariantLanguageStore).id;

	const promise = consoleApi.post<PostVariant>({
		endpoint: `/post/${postId}/variant/unpublish`,
		data: { language_id: languageId }
	});

	promise.then((res) => {
		if (updateStore) {
			updatePostVariantStore(res, true);
		}
	});

	return promise;
}

export function createPostVariant(postId: number, languageId: number) {
	return consoleApi.post<PostVariant>({
		endpoint: `/post/${postId}/variant`,
		data: { language_id: languageId }
	});
}

export function deletePostVariant() {
	const languageId = get(postVariantLanguageStore).id;

	return consoleApi.delete({
		endpoint: `/post/${get(postStore).id}/variant`,
		data: { language_id: languageId }
	});
}

export function clonePost(postId: number) {
	return consoleApi.post<Post>({
		endpoint: `/post/${postId}/clone`
	});
}

// Collaborative editing (see PostVariantCollabController on the backend). These intentionally
// don't call updatePostVariantStore themselves - submitCollabSteps's outcome is confirmed (or
// not) synchronously in its own response now (see CollabStepsResponse), and checkpointPostVariant's
// caller (SaveStatus.svelte) already knows exactly what it just wrote and updates the store itself.

// mirrors PostVariantCollabService::submitSteps()'s return shape. When accepted is false,
// `version`/`steps`/`client_ids` are the steps the caller is missing (not an echo of what it
// just sent) - feed them straight into editor.collab.receiveSteps() to catch up, instead of
// waiting on Mercure to (maybe) deliver them - see Editor.svelte's handleSendable.
export interface CollabStepsResponse {
	accepted: boolean;
	version: number;
	steps: unknown[];
	client_ids: string[];
}

export function submitCollabSteps(data: {
	post_variant_id: number;
	version: number;
	steps: unknown[];
	client_id: string;
}) {
	return consoleApi.post<CollabStepsResponse>({
		endpoint: `/documents/steps`,
		data
	});
}

// Standalone catch-up (PostVariantCollabController::sync) - call whenever the client suspects
// it missed a Mercure broadcast (e.g. its EventSource reconnecting after a drop), not only after
// a rejected submitCollabSteps. Returns the same shape minus `accepted`, since it's not a submission.
export function syncCollabSteps(data: { post_variant_id: number; version: number }) {
	return consoleApi.get<Omit<CollabStepsResponse, 'accepted'>>({
		endpoint: `/documents/sync`,
		data
	});
}

// cursor is null on blur - see @hyvor/richtext's CursorsPluginConfig.onLocalCursorChange
export function submitCollabCursor(data: {
	post_variant_id: number;
	client_id: string;
	cursor: { from: number; to: number } | null;
}) {
	return consoleApi.post<void>({
		endpoint: `/documents/cursor`,
		data: {
			post_variant_id: data.post_variant_id,
			client_id: data.client_id,
			from: data.cursor?.from ?? null,
			to: data.cursor?.to ?? null
		}
	});
}

export function checkpointPostVariant(data: {
	post_variant_id: number;
	version: number;
	content: string;
}) {
	return consoleApi.post<void>({
		endpoint: `/documents/checkpoint`,
		data
	});
}
