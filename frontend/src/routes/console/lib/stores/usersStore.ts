import { writable } from "svelte/store";
import type { User } from "../types";


/** 
 * Saved "some" users of the blog in this store.
 * This is loaded from the backend in the init call in [subdomain]/+layout.svelte
 * May not be all users, but the most important ones.
 */
export const usersStore = writable<User[]>([]);