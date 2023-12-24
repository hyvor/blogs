import { derived, get, writable } from "svelte/store";
import { languagesStore } from "../stores";
import type { Post, PostVariant } from "../types";

// originally loaded post
export const postOriginalStore = writable<Post>();

// current post data
export const postStore = writable<Post>();


export type PostSidebar = 'settings' | 'seo' | 'links' | 'ai';

export interface PostEditingStatus {
    languageId: number,
    sidebar: PostSidebar,
    isEditingPublished: boolean,
}


export const postEditingStatusStore = writable<PostEditingStatus>();


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


export function initPostEditingState() {
    postEditingStatusStore.set({
        languageId: get(languagesStore).find(l => l.is_primary === true)!.id,
        sidebar: 'settings',
        isEditingPublished: false,
    })
}


export function updatePostVariantStore(values: Partial<PostVariant>) {
    postStore.update(post => {
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
}