import type { CollabClientID, CollabStepJSON, Editor, RemoteCursorUser } from '@hyvor/richtext';
import { getConfig } from '../../../../../../lib/config';
import { fetchEventSource } from '@microsoft/fetch-event-source';

// must match DocumentService::topic() on the backend
export function collabTopic(variantId: number): string {
	return `document:${variantId}`;
}

export function applyConfirmedSteps(editor: Editor, steps: CollabStep[]) {
	const currentVersion = editor.collab.getVersion();

	if (steps.length === 0) return;

	// get the steps and client IDs where the version is greater than the current version
	const newSteps = steps.filter((step) => step.version > currentVersion);

	editor.collab.receiveSteps(
		newSteps.map((step) => step.step),
		newSteps.map((step) => step.client_id)
	);
}

// StepDto in backend
export interface CollabStep {
	version: number;
	step: CollabStepJSON;
	client_id: CollabClientID;
}

interface CollabStepsMercureMessage {
	type: 'steps';
	version: number;
	steps: CollabStep[];
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

interface CollabNewDocumentMercureMessage {
	type: 'new_document';
	content: string; // JSON
}

type CollabMercureMessage =
	CollabStepsMercureMessage | CollabCursorMercureMessage | CollabNewDocumentMercureMessage;

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
	token: string,
	onSteps: (steps: CollabStep[]) => void,
	onCursor: (message: CollabCursorMercureMessage) => void,
	onNewDocument: (message: CollabNewDocumentMercureMessage) => void
): () => void {
	const url = new URL(getConfig().mercure.public_url);
	url.searchParams.append('topic', topic);

	const controller = new AbortController();

	// tracks whether we've successfully opened before, so a later onopen call (after a drop) can
	// be told apart from the initial connect
	let connectedBefore = false;

	fetchEventSource(url.toString(), {
		signal: controller.signal,
		headers: {
			Authorization: `Bearer ${token}`
		},
		openWhenHidden: true,
		async onopen(response) {
			// if (response.ok && response.headers.get('content-type')?.startsWith(EventStreamContentType)) {
			// 	if (connectedBefore) {
			// 		onReconnect();
			// 	}
			// 	connectedBefore = true;
			// 	return;
			// }
			// throw new Error(`Failed to open Mercure subscription: ${response.status}`);
		},
		onmessage(event) {
			let message: CollabMercureMessage;
			try {
				message = JSON.parse(event.data);
			} catch {
				return;
			}

			if (message.type === 'steps' && message.steps.length > 0) {
				onSteps(message.steps);
			} else if (message.type === 'cursor') {
				onCursor(message);
			} else if (message.type === 'new_document') {
				onNewDocument(message);
			}
		},
		onerror(err) {
			if (controller.signal.aborted) {
				// rethrow to stop retrying - we're unsubscribing
				throw err;
			}
			// otherwise, swallow so fetchEventSource keeps retrying with its default backoff
		}
	}).catch(() => {
		// intentionally unhandled: onerror already deals with retry/abort decisions above
	});

	return () => {
		controller.abort();
	};
}
