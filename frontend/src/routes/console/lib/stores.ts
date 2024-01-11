import { derived, writable } from "svelte/store";
import type { AuthUser, BlogList, Post } from "./types";

// Currently logged in user
export const authUserStore = writable<AuthUser>();

// List of blogs of the current user (all roles)
export const blogListStore = writable<BlogList[]>([]);


export function addToBlogList(blog: BlogList) {
    blogListStore.update(list => [...list, blog])
}