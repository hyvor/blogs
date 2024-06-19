import consoleApi from '../../../lib/consoleApi';
import type { Webhook, WebhookEvent } from '../../../lib/types';

export enum WebhookEventType {
	BLOG_UPDATED = 'blog.updated',

	POST_CREATED = 'post.created',
	POST_UPDATED = 'post.updated',
	POST_DELETED = 'post.deleted',

	TAG_CREATED = 'tag.created',
	TAG_UPDATED = 'tag.updated',
	TAG_DELETED = 'tag.deleted',

	USER_CREATED = 'user.created',
	USER_UPDATED = 'user.updated',
	USER_DELETED = 'user.deleted',

	MEDIA_CREATED = 'media.created',
	MEDIA_DELETED = 'media.deleted',

	NAVIGATION_CHANGED = 'navigation.changed',
	ROUTES_CHANGED = 'routes.changed',
	LANGUAGES_CHANGED = 'languages.changed',

	CACHE_SINGLE = 'cache.single',
	CACHE_TEMPLATES = 'cache.templates',
	CACHE_ALL = 'cache.all'
}

export function getWebhooks() {
	return consoleApi.get<Webhook[]>({
		endpoint: '/webhooks'
	});
}

export function createWebhook(url: string, events: WebhookEvent[]) {
	return consoleApi.post<Webhook>({
		endpoint: '/webhook',
		data: {
			url,
			events
		}
	});
}

export function updateWebhook(id: number, data: Partial<Webhook>) {
	return consoleApi.patch<Webhook>({
		endpoint: `/webhook/${id}`,
		data
	});
}

export function deleteWebhook(id: number) {
	return consoleApi.delete({
		endpoint: `/webhook/${id}`
	});
}