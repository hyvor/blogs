import { derived, writable } from 'svelte/store';
import { languagesStore } from '../../../lib/stores/languagesStore';
import type { Post, PostVariant } from '../../../lib/types';
import type { Editor } from '@hyvor/richtext';

// types

export type PostSidebar = 'settings' | 'seo' | 'links' | 'ai';

// stores

export const postOriginalStore = writable<Post>();
export const postStore = writable<Post>();
export const postSidebarStore = writable<PostSidebar>('settings');
export const postVariantOriginalStore = writable<PostVariant>();
export const postVariantStore = writable<PostVariant>();
export const postEditingPublished = writable<boolean>(false); // whether currently editing a published post
export const postEditor = writable<Editor>();
// whether the active editor has local steps not yet checkpointed to content/content_unsaved -
// set by Editor.svelte's onvaluechange, cleared by SaveStatus.svelte after a successful
// checkpoint. Content itself is no longer mirrored into postVariantStore on every keystroke
// (see PostVariantCollabService on the backend) - only this dirty flag is kept live.
export const postContentDirtyStore = writable<boolean>(false);
// 'editing' | 'suggesting' - see @hyvor/richtext's EditorConfig.suggestions / SuggestionModeToggle.svelte
export const postSuggestionModeStore = writable<'editing' | 'suggesting'>('editing');

// derived

export const postVariantLanguageStore = derived(
	[postVariantStore, languagesStore],
	([postVariant, languages]) => {
		return languages.find((l) => l.id === postVariant.language_id)!;
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

export interface PostEditingStatus {}

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

export function updatePostEditingStatusValue() {
	throw new Error('Function not implemented.');
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
