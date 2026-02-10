import { derived, writable } from 'svelte/store';
import type { AuthUser, BlogList, Post } from './types';
import type { CloudContextOrganization } from '@hyvor/design/cloud';

// Currently logged in user
export const authUserStore = writable<AuthUser>();
export const authOrganizationStore = writable<CloudContextOrganization>();
// List of blogs of the current user (all roles)
export const blogListStore = writable<BlogList[]>([]);

export function addToBlogList(blog: BlogList) {
	blogListStore.update((list) => [...list, blog]);
}
