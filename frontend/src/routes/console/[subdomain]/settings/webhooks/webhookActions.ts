import consoleApi from "../../../lib/consoleApi";
import type { Webhook, WebhookEvent } from "../../../lib/types";

export const WebhookEventNames = [
    'cache.single',
    'cache.templates',
    'cache.all'
] as const;


export function getWebhooks() {
    return consoleApi.get<Webhook[]>({
        endpoint: '/webhooks'
    })
}

export function createWebhook(url: string, events: WebhookEvent[]) {
    return consoleApi.post<Webhook>({
        endpoint: '/webhook',
        data: {
            url,
            events
        }
    })
}

export function updateWebhook(id: number, data: Partial<Webhook>) {
    return consoleApi.patch<Webhook>({
        endpoint: `/webhook/${id}`,
        data
    })
}

export function deleteWebhook(id: number) {
    return consoleApi.delete({
        endpoint: `/webhook/${id}`
    })
}