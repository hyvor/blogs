import { writable } from "svelte/store";
import type { BlogList } from "./types";
import type {
  CloudContextUser,
  CloudContextOrganization,
} from "@hyvor/design/cloud";

// Currently logged in user
export const authUserStore = writable<CloudContextUser>();
export const authOrganizationStore = writable<CloudContextOrganization>();
// List of blogs of the current user (all roles)
export const blogListStore = writable<BlogList[]>([]);

export function addToBlogList(blog: BlogList) {
  blogListStore.update((list) => [...list, blog]);
}
