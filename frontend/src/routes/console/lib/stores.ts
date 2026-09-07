import { writable } from 'svelte/store';
import type { BlogList } from './types';
import type {
	CloudContextUser,
	CloudContextOrganization,
	ResolvedLicense
} from '@hyvor/design/cloud';

// Currently logged in user
export const authUserStore = writable<CloudContextUser>();
export const authOrganizationStore = writable<CloudContextOrganization>();
export const resolvedLicenseStore = writable<ResolvedLicense | null>();
// List of blogs of the current user (all roles)
export const blogListStore = writable<BlogList[]>([]);
// Whether the blog selector modal is open
export const blogSelectorOpenStore = writable(false);

export function addToBlogList(blog: BlogList) {
	blogListStore.update((list) => [...list, blog]);
}
