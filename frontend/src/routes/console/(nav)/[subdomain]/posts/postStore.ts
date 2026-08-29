import { derived, writable } from 'svelte/store';
import { languagesStore } from '../../../lib/stores/languagesStore';
import type { Document, Post, PostVariant } from '../../../lib/types';
import type { Editor } from '@hyvor/richtext';

// types

export type PostSidebar = 'settings' | 'seo' | 'links' | 'ai';

// stores

export const postOriginalStore = writable<Post>();
export const postStore = writable<Post>();
export const postSidebarStore = writable<PostSidebar | null>(null);
export const postVariantOriginalStore = writable<PostVariant>();
export const postVariantStore = writable<PostVariant>();
export const postEditingPublished = writable<boolean>(false);
export const postEditor = writable<Editor>();
export const postTitle = writable<{ focus: () => void; focusAtEnd: () => void }>();
export const postContentDirtyStore = writable<boolean>(false);
export const postSuggestionModeStore = writable<'editing' | 'suggesting'>('editing');

// derived

export const postVariantLanguageStore = derived(
	[postVariantStore, languagesStore],
	([postVariant, languages]) => {
		return languages.find((l) => l.id === postVariant.language_id)!;
	}
);

export const documentStore = writable<Document>();

export function updateDocumentStore(values: Partial<Document>) {
	documentStore.update((doc) => ({
		...doc,
		...values
	}));
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
