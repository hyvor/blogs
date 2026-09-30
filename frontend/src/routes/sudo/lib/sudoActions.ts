import type { Blog } from '../types';
import sudoApi from './sudoApi';

export function blockBlog(id: number | string): Promise<Blog> {
	return sudoApi.post<Blog>({ endpoint: `/blogs/${id}/block` });
}

export function unblockBlog(id: number | string): Promise<Blog> {
	return sudoApi.post<Blog>({ endpoint: `/blogs/${id}/unblock` });
}
