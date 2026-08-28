import { get } from 'svelte/store';
import { blogStore } from './stores/blogStore';

export function consoleUrl(path: string) {
	path = path.replace(/^\//, '');
	return '/console/' + path;
}

export function consoleUrlWithBlog(path: string) {
	const blogSubdomain = get(blogStore).subdomain;
	path = path.replace(/^\//, '');
	return consoleUrl(`${blogSubdomain}/${path}`);
}
