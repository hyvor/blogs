import { get } from "svelte/store";
import { languagesStore } from "../../lib/stores/languagesStore";
import type { Post, PostVariant } from "../../lib/types";
import consoleApi from "../../lib/consoleApi";
import { postEditingStatusStore, postLanguageStore, postStore, updatePostVariantStore } from "./postStore";


// API

interface GetPostsData {
    status?: 'featured' | 'published' | 'draft' | 'scheduled',
    author_id?: number,
    tag_id?: number,
    start_timestamp?: number, // unix timestamp
    end_timestamp?: number, // unix timestamp
    search?: string,
    language_id?: number,
    limit?: number, // default 50, max 100
    offset?: number,
}


export function getPosts(data: GetPostsData) {
    return consoleApi.get<Post[]>({
        endpoint: "/posts",
        data
    });
}

export function getPages() {
    return consoleApi.get<Post[]>({
        endpoint: "/pages",
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
export function updatePostVariant(data: Partial<PostVariant>, updateStore = true) {

    const postId = get(postStore).id;
    const languageId = get(postLanguageStore).id;
    data.language_id = languageId;

    const promise = consoleApi.patch<PostVariant>({
        endpoint: `/post/${postId}/variant`,
        data
    })

    promise.then(res => {
 
        if (updateStore) {
            // update only the fields that were changed
            const update = {} as Partial<PostVariant>;
            Object.keys(data).forEach(key => 
                (update as any)[key] = (res as any)[key]
            );
            updatePostVariantStore(res, true);
        }

    });

    return promise;

}

export function createPostVariant(postId: number, languageId: number) {

    return consoleApi.post<PostVariant>({
        endpoint: `/post/${postId}/variant`,
        data: { language_id: languageId }
    })

}