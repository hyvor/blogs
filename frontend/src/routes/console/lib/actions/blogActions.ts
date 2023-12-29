import consoleApi from "../consoleApi";
import { updateBlogStore } from "../stores/blogStore";
import type { Blog } from "../types";

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