import { get } from "svelte/store";
import { languagesStore } from "../stores";
import type { Post, PostVariant } from "../types";
import consoleApi from "../consoleApi";
import { postEditingStatusStore, postLanguageStore, postStore } from "../stores/postStore";


// API

export function updatePost(data: Partial<Post>) {
    
    const promise = consoleApi.patch<Post>({
        endpoint: `/post/${get(postStore).id}`,
        data
    });

    promise.then(res => {
        postStore.set(res);
    });

}

// updates current post variant
export function updatePostVariant(data: Partial<PostVariant>) {

    const postId = get(postStore).id;
    const languageId = get(postLanguageStore).id;
    data.language_id = languageId;

    const promise = consoleApi.patch<PostVariant>({
        endpoint: `/post/${postId}/variant`,
        data
    })

    promise.then(res => {
        postStore.update(post => {
            post.variants = post.variants.map(v => {
                if (v.language_id === languageId) {
                    return res;
                }
                return v;
            });

            return post;
        });
    });

    return promise;

}