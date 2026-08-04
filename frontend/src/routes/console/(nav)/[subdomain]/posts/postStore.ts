import { derived, get, writable } from 'svelte/store';
import { languagesStore } from '../../../lib/stores/languagesStore';
import type { Post, PostVariant } from '../../../lib/types';
import type { EditorView } from 'prosemirror-view';

// types

export type PostSidebar = 'settings' | 'seo' | 'links' | 'ai';

// stores

export const postOriginalStore = writable<Post>();
export const postStore = writable<Post>();
export const postSidebarStore = writable<PostSidebar>('settings');
export const postVariantOriginalStore = writable<PostVariant | null>(null);
export const postVariantStore = writable<PostVariant | null>(null);
export const postEditingPublished = writable<boolean>(false); // whether currently editing a published post

// derived

export const postVariantLanguageStore = derived(
	[postVariantStore, languagesStore],
	([postVariant, languages]) => {
		if (!postVariant) return null;
		return languages.find((l) => l.id === postVariant.language_id) || null;
	}
);

export const postCurrentContentKey = derived(
	[postVariantStore, postEditingPublished],
	([postVariant, postEditingPublished]): 'content' | 'content_unsaved' => {
		if (!postVariant) return 'content';
		if (postVariant.status === 'draft') return 'content';

		if (postEditingPublished) {
			// content_unsaved is set (editing started)
			if (postVariant.content_unsaved) {
				return 'content_unsaved';
			} else {
				// otherwise start from content
				return 'content';
			}
		}

		return 'content';
	}
);

export interface PostEditingStatus {
	languageId: number;
	sidebar: PostSidebar;
	isEditingPublished: boolean;
	editorView: EditorView | null;
	postView: HTMLDivElement;
	isSaving: boolean;
	editorVersion: number; // to force re-rendering of editor
}

export const postEditingStatusStore = writable<PostEditingStatus>();

// export function initPostEditingState(postView: HTMLDivElement, langId: number) {
// 	postEditingStatusStore.set({
// 		languageId: langId,
// 		sidebar: 'settings',
// 		isEditingPublished: false,
// 		editorView: null,
// 		postView,
// 		isSaving: false,
// 		editorVersion: 0
// 	});
// }

export function updatePostEditingStatusValue<T extends keyof PostEditingStatus>(
	key: T,
	value: PostEditingStatus[T]
) {
	postEditingStatusStore.update((status) => {
		return {
			...status,
			[key]: value
		};
	});
}

export function increaseEditorVersion() {
	postEditingStatusStore.update((status) => {
		return {
			...status,
			editorVersion: status.editorVersion + 1
		};
	});
}

export function updatePostStore(values: Partial<Post>, original = false) {
	const stores = [postStore];
	if (original) {
		stores.push(postOriginalStore);
	}

	stores.forEach((store) => {
		store.update((post) => {
			return {
				...post,
				...values
			};
		});
	});
}

export function updatePostVariantStore(values: Partial<PostVariant>, original = false) {
	const stores = [postVariantStore];
	if (original) {
		stores.push(postVariantOriginalStore);
	}

	stores.forEach((store) => {
		store.update((variant) => {
			if (!variant) return variant;
			return {
				...variant,
				...values
			};
		});
	});
}

export function addPostVariantStore(variant: PostVariant, original = true) {
	const stores = [postStore];
	if (original) {
		stores.push(postOriginalStore);
	}
	stores.forEach((store) => {
		store.update((post) => {
			post.variants.push(variant);
			return post;
		});
	});
}

export function removePostVariantStore(languageId: number, original = true) {
	const stores = [postStore];
	if (original) {
		stores.push(postOriginalStore);
	}

	stores.forEach((store) => {
		store.update((post) => {
			post.variants = post.variants.filter((v) => v.language_id !== languageId);
			return post;
		});
	});
}

// export function setPostAndPostOriginalStore(post: Post) {
// 	postStore.set({ ...post });
// 	postOriginalStore.set({ ...post });
// }
