import { get } from "svelte/store";
import type { Post, PostVariant, User, Tag } from "../../lib/types";
import consoleApi from "../../lib/consoleApi";
import { postLanguageStore, postStore, removePostVariantStore, updatePostStore, updatePostVariantStore } from "./postStore";


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

export function updatePost(data: Partial<Post>, updateStore = true) {
    
    const promise = consoleApi.patch<Post>({
        endpoint: `/post/${get(postStore).id}`,
        data
    });

    promise.then(res => {

        if (updateStore) {
            // update only the fields that were changed
            const update = {} as Partial<Post>;
            Object.keys(data).forEach(key => 
                (update as any)[key] = (res as any)[key]
            );
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

export function updatePostAuthors(authors: User[], updateStore = true) {

    const postId = get(postStore).id;

    const promise = consoleApi.patch({
        endpoint: `/post/${postId}/authors`,
        data: {
            ids: authors.map(author => author.id)
        }
    })

    promise.then(() => {
        if (updateStore) {
            updatePostStore({authors}, true);
        }
    });

    return promise;

}

export function updatePostTags(tags: Tag[], updateStore = true) {

    const postId = get(postStore).id;

    const promise = consoleApi.patch({
        endpoint: `/post/${postId}/tags`,
        data: {
            ids: tags.map(tag => tag.id)
        }
    })

    promise.then(() => {
        if (updateStore) {
            updatePostStore({tags}, true);
        }
    });

    return promise;

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
            updatePostVariantStore(update, true);
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

export function deletePostVariant() {
    const languageId = get(postLanguageStore).id;

    return consoleApi.delete({
        endpoint: `/post/${get(postStore).id}/variant`,
        data: { language_id: languageId }
    });
}