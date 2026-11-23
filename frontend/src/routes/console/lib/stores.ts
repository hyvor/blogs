import { derived, writable } from 'svelte/store';
import type { BlogList } from './types';
import type {
	CloudContextUser,
	CloudContextOrganization,
	ResolvedLicense
} from '@hyvor/design/cloud';

// Currently logged in user
export const authUserStore = writable<CloudContextUser>();
export const authOrganizationStore = writable<CloudContextOrganization>();
export const hasPaymentMethodStore = writable<boolean | null>(null);
export const resolvedLicenseStore = writable<ResolvedLicense | null>();
// usage of the current organization from /init (limits come from the license)
export interface OrgUsage {
	blogs: number;
}
export const orgUsageStore = writable<OrgUsage | null>(null);

// limits of the current organization (-1 = unlimited), derived from the license
export interface OrgLimits {
	users: number;
	blogs: number;
	blogs_used: number;
	languages: number;
}
export const orgLimitsStore = derived(
	[resolvedLicenseStore, orgUsageStore],
	([$license, $usage]): OrgLimits | null => {
		if (!$usage) return null;
		const l = $license?.license;
		const limit = (key: string) => {
			const v = l?.[key];
			return typeof v === 'number' ? v : -1;
		};
		return {
			users: limit('users'),
			blogs: limit('blogs'),
			blogs_used: $usage.blogs,
			languages: limit('languages')
		};
	}
);
// List of blogs of the current user (all roles)
export const blogListStore = writable<BlogList[]>([]);
// Whether the blog selector modal is open
export const blogSelectorOpenStore = writable(false);

export function addToBlogList(blog: BlogList) {
	blogListStore.update((list) => [...list, blog]);
}
