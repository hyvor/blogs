import consoleApi from '../../lib/consoleApi';
import { blogCountsStore, blogOriginalStore, blogStore } from '../../lib/stores/blogStore';
import { languagesStore } from '../../lib/stores/languagesStore';
import { usersStore } from '../../lib/stores/usersStore';
import { subscriptionStore, usageStore } from '../../lib/stores/subscriptionStore';
import type { Blog, BlogCounts, Language, Subscription, UsageTypes, User } from '../../lib/types';
import { isTempStore } from '../../lib/temp';
import { get } from 'svelte/store';

interface BlogResponse {
	blog: Blog;
	languages: Language[];
	users: User[];
	subscription: Subscription | null;
	usage: UsageTypes;
	counts: BlogCounts;
}

// to prevent multiple requests for the same subdomain
const LOADER_PROMISES: Record<string, Promise<BlogResponse>> = {};

export function loadBlog(subdomain: string) {
	if (LOADER_PROMISES[subdomain]) {
		return LOADER_PROMISES[subdomain];
	}

	const promise = new Promise<BlogResponse>((resolve, reject) => {
		consoleApi
			.get<BlogResponse>({
				endpoint: '/blog',
				subdomain
			})
			.then((res) => {
				if (res.blog.type === 'temp' && !get(isTempStore)) {
					location.href = '/console';
				}

				blogStore.set(res.blog);
				blogOriginalStore.set(res.blog);
				blogCountsStore.set(res.counts);
				languagesStore.set(res.languages);
				usersStore.set(res.users);
				subscriptionStore.set(res.subscription);
				usageStore.set(res.usage);

				resolve(res);
			})
			.catch((err) => {
				reject(err);
			})
			.finally(() => {
				delete LOADER_PROMISES[subdomain];
			});
	});

	LOADER_PROMISES[subdomain] = promise;

	return promise;
}
