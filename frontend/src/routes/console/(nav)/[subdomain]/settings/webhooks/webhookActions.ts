import consoleApi from '../../../../lib/consoleApi';
import type { Webhook, WebhookEventType } from '../../../../lib/types';

export function getWebhooks() {
	return consoleApi.get<Webhook[]>({
		endpoint: '/webhooks'
	});
}

export function createWebhook(url: string, events: WebhookEventType[]) {
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