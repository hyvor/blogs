import type { CollabClientID, CollabStepJSON } from '@hyvor/richtext';
import { getConfig } from '../../../../../../lib/config';

export type CollabContentType = 'content' | 'content_unsaved';

// must match PostVariantCollabService::topic() on the backend
export function collabTopic(variantId: number, type: CollabContentType): string {
	return `post_variant_collab:${variantId}:${type}`;
}

interface CollabMercureMessage {
	type: 'steps';
	version: number;
	steps: CollabStepJSON[];
	client_ids: CollabClientID[];
}

/**
 * Subscribes to a post variant's collab topic on the Mercure hub (see PostController::getPost,
 * which sets the subscription cookie this relies on) and forwards accepted step batches -
 * including this client's own, once confirmed - to `onSteps`. Returns an unsubscribe function.
 */
export function subscribeToCollabTopic(
	topic: string,
	onSteps: (steps: CollabStepJSON[], clientIds: CollabClientID[]) => void
): () => void {
	const url = new URL(getConfig().mercure.public_url);
	url.searchParams.append('topic', topic);

	const source = new EventSource(url.toString(), { withCredentials: true });

	source.onmessage = (event) => {
		let message: CollabMercureMessage;
		try {
			message = JSON.parse(event.data);
		} catch {
			return;
		}

		if (message.type === 'steps' && message.steps.length > 0) {
			onSteps(message.steps, message.client_ids);
		}
	};

	source.onerror = () => {
		// EventSource retries automatically; nothing to do here besides not crashing the tab
	};

	return () => source.close();
}
