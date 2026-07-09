import consoleApi from '../../lib/consoleApi';
import { blogCountsStore, blogOriginalStore, blogStore } from '../../lib/stores/blogStore';
import { languagesStore } from '../../lib/stores/languagesStore';
import { usersStore } from '../../lib/stores/usersStore';
import type { Blog, BlogCounts, Language, User } from '../../lib/types';
import { isTempStore } from '../../lib/temp';
import { get } from 'svelte/store';

export interface BlogResponse {
	blog: Blog;
	languages: Language[];
	users: User[];
	counts: BlogCounts;
}

// to prevent multiple requests for the same subdomain
const LOADER_PROMISES: Record<string, Promise<BlogResponse>> = {};
const PRELOADED_BLOGS: Record<string, BlogResponse> = {};

export function loadBlog(subdomain: string) {
	if (LOADER_PROMISES[subdomain]) {
		return LOADER_PROMISES[subdomain];
	}

	if (PRELOADED_BLOGS[subdomain]) {
		handleResponse(PRELOADED_BLOGS[subdomain]);
		delete PRELOADED_BLOGS[subdomain];
		return Promise.resolve(PRELOADED_BLOGS[subdomain]);
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
	if (res.blog.type === 'temp' && !get(isTempStore)) {
		location.href = '/console';
	}

	blogStore.set(res.blog);
	blogOriginalStore.set(res.blog);
	blogCountsStore.set(res.counts);
	languagesStore.set(res.languages);
	usersStore.set(res.users);
}

export function setPreloadedBlog(blogResponse: BlogResponse) {
	PRELOADED_BLOGS[blogResponse.blog.subdomain] = blogResponse;
}
