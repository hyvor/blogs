import { writable } from "svelte/store";
import type { Blog } from "../types";

/**
 * CURRENT BLOG START ===========
 */
// current blog data


export const blogStore = writable<Blog>();
