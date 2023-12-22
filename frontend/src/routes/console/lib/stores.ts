import { writable } from "svelte/store";
import type { AuthUser, Blog, BlogList, Language } from "./types";

// Currently logged in user
export const authUserStore = writable<AuthUser>();

// List of blogs of the current user (all roles)
export const blogListStore = writable<BlogList[]>([]);


/**
 * CURRENT BLOG START ===========
 */

// current blog data
export const blogStore = writable<Blog>();

// current blog's languages
export const languagesStore = writable<Language[]>([]);


/**
 * CURRENT BLOG END ===========
 */
