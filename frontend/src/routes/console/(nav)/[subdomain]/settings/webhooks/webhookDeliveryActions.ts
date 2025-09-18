import consoleApi from '../../../../lib/consoleApi';
import type { WebhookDelivery } from '../../../../lib/types';

export interface WebhookDeliveryResponse {
	data: WebhookDelivery[];
	total: number;
}

export function getWebhookDeliveries(
	webhookId?: number,
	limit: number = 50,
	offset: number = 0
): Promise<WebhookDelivery[]> {
	const data: Record<string, any> = {
		limit,
		offset
	};
	
	if (webhookId) {
		data.webhook_id = webhookId;
	}

	return consoleApi.get<WebhookDelivery[]>({
		endpoint: '/webhook-deliveries',
		data
	});
}
