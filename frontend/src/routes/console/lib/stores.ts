import { derived, writable } from "svelte/store";
import type { AuthUser, Blog, BlogList, Post } from "./types";

// Currently logged in user
export const authUserStore = writable<AuthUser>();

// List of blogs of the current user (all roles)
export const blogListStore = writable<BlogList[]>([]);


/**
 * CURRENT BLOG START ===========
 */

// current blog data
export const blogStore = writable<Blog>();


