import { writable } from 'svelte/store';
import type { Blog, BlogCounts, BlogVariant, HostingInfo, BlogIntegrations } from '../types';

export const blogStore = writable<Blog>();
export const blogOriginalStore = writable<Blog>();
export const blogCountsStore = writable<BlogCounts>();
export const hostingInfoStore = writable<HostingInfo>();
export const integrationsStore = writable<BlogIntegrations>({ hyvor_talk: null, hyvor_post: null });

export function updateBlogStore(
	blog: Partial<Blog> | ((currentBlog: Blog) => Partial<Blog>),
	original = false
) {
	const stores = [blogStore];
	if (original) {
		stores.push(blogOriginalStore);
	}
	stores.forEach((store) => {
		store.update((b) => {
			const val = typeof blog === 'function' ? blog(b) : blog;
			return { ...b, ...val };
		});
	});
}

export function updateBlogStoreVariantValue<T extends keyof BlogVariant>(
	language_id: number,
	key: T,
	value: BlogVariant[T]
) {
	blogStore.update((blog) => {
		return {
			...blog,
			variants: blog.variants.map((v) => {
				if (v.language_id === language_id) {
					return { ...v, [key]: value };
				}
				return v;
			})
		};
	});
}

export function updateBlogStoreVariant(
	language_id: number,
	variant: Partial<BlogVariant>,
	original = false
) {
	const stores = [blogStore];
	if (original) {
		stores.push(blogOriginalStore);
	}

	stores.forEach((store) => {
		store.update((blog) => {
			return {
				...blog,
				variants: blog.variants.map((v) => {
					if (v.language_id === language_id) {
						return {
							...v,
							...variant
						};
					}
					return v;
				})
			};
		});
	});
}

export function updateHostingInfoStore(updates: HostingInfo) {
	blogStore.update((blog) => ({
		...blog,
		hosting_at: updates.hosting_at,
		hosting_domain: updates.custom_domain?.domain ?? null,
		hosting_url: updates.hosting_url ?? null
	}));
	hostingInfoStore.set(updates);
}

// integrations
export function setHyvorPostIntegrationState(newsletterId: number | null) {
	integrationsStore.update((integrations) => ({
		...integrations,
		hyvor_post: newsletterId ? { newsletter_id: newsletterId } : null
	}));
}
