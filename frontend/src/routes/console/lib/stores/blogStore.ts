import { writable } from "svelte/store";
import type { Blog } from "../types";

export const blogStore = writable<Blog>();
export const blogOriginalStore = writable<Blog>();

export function updateBlogStore(blog: Partial<Blog>, original = false) {
    const stores = [blogStore];
    if (original) {
        stores.push(blogOriginalStore);
    }
    stores.forEach(store => {
        store.update(b => {
            return {...b, ...blog};
        })
    })
}