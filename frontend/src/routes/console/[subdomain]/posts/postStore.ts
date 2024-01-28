import { derived, get, writable } from "svelte/store";
import { languagesStore } from "../../lib/stores/languagesStore";
import type { Post, PostVariant } from "../../lib/types";
import type { DOMEventMap, EditorView } from "prosemirror-view";

// originally loaded post
export const postOriginalStore = writable<Post>();

// current post data
export const postStore = writable<Post>();


export type PostSidebar = 'settings' | 'seo' | 'links' | 'ai';

export interface PostEditingStatus {
    languageId: number,
    sidebar: PostSidebar,
    isEditingPublished: boolean,
    editorView: EditorView | null,
    postView: HTMLDivElement,
    isSaving: boolean,
    editorVersion: number, // to force re-rendering of editor
}

export const postEditingStatusStore = writable<PostEditingStatus>();

export function initPostEditingState(postView: HTMLDivElement) {
    postEditingStatusStore.set({
        languageId: get(languagesStore).find(l => l.is_primary === true)!.id,
        sidebar: 'settings',
        isEditingPublished: false,
        editorView: null,
        postView,
        isSaving: false,
        editorVersion: 0
    })
}

export function updatePostEditingStatusValue<T extends keyof PostEditingStatus>(key: T, value: PostEditingStatus[T]) {
    postEditingStatusStore.update(status => {
        return {
            ...status,
            [key]: value
        }
    })
}

export function increaseEditorVersion() {
    postEditingStatusStore.update(status => {
        return {
            ...status,
            editorVersion: status.editorVersion + 1
        }
    })
}

export const postOriginalVariantStore = derived(
    [postOriginalStore, postEditingStatusStore],
    ([post, postEditingStatus]) => {
        return post.variants.find(v => v.language_id === postEditingStatus.languageId)!;
    }
)

export const postVariantStore = derived(
    [postStore, postEditingStatusStore],
    ([post, postEditingStatus]) => {
        return post.variants.find(v => v.language_id === postEditingStatus.languageId)!;
    }
)

export const postLanguageStore = derived(
    [languagesStore, postEditingStatusStore],
    ([languages, postEditingStatus]) => {
        return languages.find(l => l.id === postEditingStatus.languageId)!;
    }
)

export const postCurrentContentKey = derived(
    [postVariantStore, postEditingStatusStore],
    ([postVariant, postEditingStatus]) => {

        if (postVariant.status === 'draft') 
            return 'content';

        // editing published
        if (postEditingStatus.isEditingPublished) {
            // content_unsaved is set (editing started)
            if (postVariant.content_unsaved) {
                return 'content_unsaved'
            } else {
                // otherwise start from content
                return 'content';
            }
        }

        return 'content';
    }
)

export const postCurrentContentStore = derived(
    [postVariantStore, postCurrentContentKey],
    ([postVariant, key]) => {
        return postVariant[key];
    }
)


export function updatePostStore(values: Partial<Post>, original = false) {
    const stores = [postStore]
    if (original) {
        stores.push(postOriginalStore);
    }

    stores.forEach(store => {
        store.update(post => {
            return {
                ...post,
                ...values
            }
        });
    });
}


export function updatePostVariantStore(values: Partial<PostVariant>, original = false) {

    const stores = [postStore]
    if (original) {
        stores.push(postOriginalStore);
    }

    stores.forEach(store => {
        store.update(post => {
            const languageId = get(postLanguageStore).id;
            post.variants = post.variants.map(v => {
                if (v.language_id === languageId) {
                    return {
                        ...v,
                        ...values
                    }
                }
                return v;
            });

            return post;
        });
    });
}

export function addPostVariantStore(variant: PostVariant, original =  true) {
    const stores = [postStore]
    if (original) {
        stores.push(postOriginalStore);
    }
    stores.forEach(store => {
        store.update(post => {
            post.variants.push(variant);
            return post;
        });
    });
}

export function removePostVariantStore(languageId: number, original = true) {

    const stores = [postStore]
    if (original) {
        stores.push(postOriginalStore);
    }

    stores.forEach(store => {
        store.update(post => {
            post.variants = post.variants.filter(v => v.language_id !== languageId);
            return post;
        });
    });

}


export function setPostAndPostOriginalStore(post: Post) {
    postStore.set({...post});
    postOriginalStore.set({...post});
}