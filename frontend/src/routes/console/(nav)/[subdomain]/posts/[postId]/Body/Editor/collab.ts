import type { CollabClientID, CollabStepJSON, RemoteCursorUser } from '@hyvor/richtext';
import { getConfig } from '../../../../../../lib/config';

export type CollabContentType = 'content' | 'content_unsaved';

// must match PostVariantCollabService::topic() on the backend
export function collabTopic(variantId: number, type: CollabContentType): string {
	return `post_variant_collab:${variantId}:${type}`;
}

interface CollabStepsMercureMessage {
	type: 'steps';
	version: number;
	steps: CollabStepJSON[];
	client_ids: CollabClientID[];
}

// mirrors PostVariantCollabService::publishCursor()'s payload - `clear` means the sender
// blurred the editor (or has no resolvable blog user), so their cursor should be removed
interface CollabCursorMercureMessage {
	type: 'cursor';
	client_id: string;
	clear?: true;
	from?: number;
	to?: number;
	user?: RemoteCursorUser;
}

type CollabMercureMessage = CollabStepsMercureMessage | CollabCursorMercureMessage;

/**
 * Subscribes to a post variant's collab topic on the Mercure hub (see PostController::getPost,
 * which sets the subscription cookie this relies on) and forwards accepted step batches -
 * including this client's own, once confirmed - to `onSteps`, and other clients' cursor
 * moves/clears to `onCursor`. Returns an unsubscribe function.
 */
export function subscribeToCollabTopic(
	topic: string,
	onSteps: (steps: CollabStepJSON[], clientIds: CollabClientID[]) => void,
	onCursor: (message: CollabCursorMercureMessage) => void
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
		} else if (message.type === 'cursor') {
			onCursor(message);
		}
	};

	source.onerror = () => {
		// EventSource retries automatically; nothing to do here besides not crashing the tab
	};

	return () => source.close();
}
