import type { CollabClientID, CollabStepJSON, RemoteCursorUser } from '@hyvor/richtext';
import { getConfig } from '../../../../../../lib/config';

// must match PostVariantCollabService::topic() on the backend
export function collabTopic(variantId: number): string {
	return `post_variant_collab:${variantId}`;
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

// Guards against two overlapping EventSources for the same topic - e.g. an effect re-running
// (Svelte HMR, or a dependency changing) before its previous cleanup has run. Every incoming
// Mercure message would otherwise be delivered twice, doubling every step/cursor update handled
// downstream. Keyed by topic since each browser tab may legitimately hold subscriptions to
// several topics (different post variants) at once.
const activeSources = new Map<string, EventSource>();

/**
 * Subscribes to a post variant's collab topic on the Mercure hub (see PostController::getPost,
 * which sets the subscription cookie this relies on) and forwards accepted step batches -
 * including this client's own, once confirmed - to `onSteps`, and other clients' cursor
 * moves/clears to `onCursor`. Returns an unsubscribe function.
 *
 * `onReconnect` fires when the EventSource reconnects after a drop (not on the initial
 * connect) - Mercure has no history/replay for a subscriber that was briefly disconnected, so
 * whatever was published in that gap is simply gone from this transport's point of view. The
 * caller is expected to treat this as "go fetch what I missed" (see PostVariantCollabController's
 * `sync` endpoint) rather than trusting steps to keep arriving here uninterrupted.
 */
export function subscribeToCollabMercureTopic(
	topic: string,
	onSteps: (steps: CollabStepJSON[], clientIds: CollabClientID[], version: number) => void,
	onCursor: (message: CollabCursorMercureMessage) => void,
	onReconnect: () => void
): () => void {

	// TODO: subscribing to a public topic. This should be private

	// a still-open subscription for this exact topic means someone forgot to unsubscribe (or
	// hasn't yet) - close it rather than let two EventSources double-deliver every message
	activeSources.get(topic)?.close();

	const url = new URL(getConfig().mercure.public_url);
	url.searchParams.append('topic', topic);

	const source = new EventSource(url.toString(), { withCredentials: true });
	activeSources.set(topic, source);

	let droppedConnection = false;

	source.onmessage = (event) => {
		let message: CollabMercureMessage;
		try {
			message = JSON.parse(event.data);
		} catch {
			return;
		}

		if (message.type === 'steps' && message.steps.length > 0) {
			onSteps(message.steps, message.client_ids, message.version);
		} else if (message.type === 'cursor') {
			onCursor(message);
		}
	};

	source.onerror = () => {
		// EventSource retries automatically; just remember we dropped so the next successful
		// open can be told apart from the initial one
		droppedConnection = true;
	};

	source.onopen = () => {
		if (droppedConnection) {
			droppedConnection = false;
			onReconnect();
		}
	};

	return () => {
		source.close();
		// only clear the map entry if we're still the current holder - a newer subscription for
		// this topic may have already replaced us (see the .close() call above)
		if (activeSources.get(topic) === source) {
			activeSources.delete(topic);
		}
	};
}
