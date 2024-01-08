import { get } from "svelte/store";
import consoleApi from "../consoleApi";
import { blogOriginalStore, blogStore, updateBlogStore, updateBlogStoreVariant } from "../stores/blogStore";
import type { Blog, BlogVariant } from "../types";

export function saveSort(ids: number[]) {
    return consoleApi.patch({
        endpoint: '/blogs/sort',
        data: {blog_ids: ids},
        userApi: true
    })
}

export function updateBlog(data: Partial<Blog>, updateStore = true) {
    const promise = consoleApi.patch<Blog>({
        endpoint: '/blog',
        data
    })

    if (updateStore) {
        promise.then(res => {

            const obj = {} as Partial<Blog>;

            Object.entries(data).forEach(([key, value]) => {
                // @ts-ignore
                obj[key] = res[key];
            });

            updateBlogStore(data, true);
        })
    }

    return promise;
}

export function updateBlogVariant(languageId: number, data: Partial<BlogVariant>, updateStore = true) {
    const promise = consoleApi.patch<BlogVariant>({
        endpoint: '/blog/variant',
        data: {
            language_id: languageId,
            ...data
        }
    })
    if (updateStore) {
        promise.then(res => {
            updateBlogStoreVariant(res.language_id, data, true);
        })
    }
    return promise;
}

export function createBlogVariant(languageId: number, updateStore = true) {
    const promise = consoleApi.post<BlogVariant>({
        endpoint: '/blog/variant',
        data: {
            language_id: languageId
        }
    })

    if (updateStore) {

        promise.then(res => {
            updateBlogStore(blog => {
                return {
                    ...blog,
                    variants: [...blog.variants, res]
                }
            }, true);
        })
    
    }

    return promise;
}