import consoleApi from '../../lib/consoleApi';
import { blogCountsStore, blogOriginalStore, blogStore, licenseStore } from '../../lib/stores/blogStore';
import { languagesStore } from '../../lib/stores/languagesStore';
import { usersStore } from '../../lib/stores/usersStore';
import type { Blog, BlogCounts, Language, License, User } from '../../lib/types';
import { isTempStore } from '../../lib/temp';
import { get } from 'svelte/store';

interface BlogResponse {
	blog: Blog;
	languages: Language[];
	users: User[];
	counts: BlogCounts;
	license: License | null
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
				licenseStore.set(res.license);

				if (get(isTempStore)) {
					licenseStore.set({
						users: 2,
						storage: 10 * 1024 * 1024,
						aiTokens: 500,
						autoTranslationsChars: 500,
						talkCredits: 0,
						postEmails: 0,
						analyses: true,
					})
				}

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
