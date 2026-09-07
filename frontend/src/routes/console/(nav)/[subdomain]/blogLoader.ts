import consoleApi from '../../lib/consoleApi';
import {
	blogCountsStore,
	blogOriginalStore,
	blogStore,
	integrationsStore
} from '../../lib/stores/blogStore';
import { languagesStore } from '../../lib/stores/languagesStore';
import { usersStore } from '../../lib/stores/usersStore';
import type { Blog, BlogCounts, Language, User, BlogIntegrations, Scope } from '../../lib/types';
import { setScopes } from '../../lib/scope.svelte';

export interface BlogResponse {
	blog: Blog;
	languages: Language[];
	users: User[];
	counts: BlogCounts;
	integrations: BlogIntegrations;
	scopes?: Scope[];
}

// to prevent multiple requests for the same subdomain
const LOADER_PROMISES: Record<string, Promise<BlogResponse>> = {};
const PRELOADED_BLOGS: Record<string, BlogResponse> = {};

export function loadBlog(subdomain: string) {
	if (LOADER_PROMISES[subdomain]) {
		return LOADER_PROMISES[subdomain];
	}

	if (PRELOADED_BLOGS[subdomain]) {
		const res = PRELOADED_BLOGS[subdomain];
		handleResponse(res);
		delete PRELOADED_BLOGS[subdomain];
		return Promise.resolve(res);
	}

	const promise = new Promise<BlogResponse>((resolve, reject) => {
		consoleApi
			.get<BlogResponse>({
				endpoint: '/blog',
				subdomain
			})
			.then((res) => {
				handleResponse(res);
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

function handleResponse(res: BlogResponse) {
	blogStore.set(res.blog);
	blogOriginalStore.set(res.blog);
	blogCountsStore.set(res.counts);
	languagesStore.set(res.languages);
	usersStore.set(res.users);
	integrationsStore.set(res.integrations);
	setScopes(res.scopes ?? []);
}

export function setPreloadedBlog(blogResponse: BlogResponse) {
	PRELOADED_BLOGS[blogResponse.blog.subdomain] = blogResponse;
}
