import { get } from 'svelte/store';
import { authOrganizationStore } from '../../../lib/stores';
import consoleApi, { getConsoleBlogBaseUrl } from '../../../lib/consoleApi';
import type { AiConversation, AiDocumentChangePostVariant, AiMessageEvent, PostVariant } from '../../../lib/types';

export const DEFAULT_CONTENT_JSON = '{"type":"doc","content":[{"type":"paragraph","content":[]}]}';

export type AgentEvent =
	| { type: 'conversation_created'; conversation: AiConversation; }
	| { type: 'text_chunk', content: string }
	| { type: 'thinking_chunk', content: string}
	| { type: 'event', event: AiMessageEvent }
	| { type: 'done'};

export type AgentBlock =
	| { type: 'thinking'; content: string; done: boolean }
	| { type: 'tool'; name: string; status: 'running' | 'done' }
	| { type: 'text'; content: string }
	| { type: 'variant_activity'; postVariantId: number; reads: number; edits: number };

export interface DocumentChange {
	postVariant: AiDocumentChangePostVariant;
	content: string;
	version: number; // version agent edited
}

export interface CurrentDocument {
	version: number;
	content: string | null;
	// the collab document_version - distinct from `version` (content_unsaved_version) above -
	// needed to call applyDocumentChange below without a live collab session
	document_version: number;
}

// used by DiffReviewModal to diff a suggested change against the post's actual current
// content (rather than a blank document), and to detect if the post has since been edited
export function getCurrentDocumentForVariant(postVariantId: number) {
	return consoleApi.get<CurrentDocument>({
		endpoint: '/documents/variant',
		data: { post_variant_id: postVariantId }
	});
}

// Persists a document change via the collab checkpoint endpoint, keyed directly by
// post_variant_id - no post id/language id or live editor needed. 409s if `documentVersion`
// is behind the post's live document_version (edited elsewhere since) - see
// DocumentsController::checkpoint. Used by the whole-blog agent page (AgentChat.svelte); the
// sidebar agent applies through the live editor's collab pipeline instead (see
// saveAgentDocumentChange above).
export function applyDocumentChange(postVariantId: number, content: string, documentVersion: number) {
	return consoleApi.post<void>({
		endpoint: '/documents/checkpoint',
		data: { post_variant_id: postVariantId, content, version: documentVersion }
	});
}

export async function callAgent(
	prompt: string,
	postVariantId: number | null,
	conversationId: number | null,
	onEvent: (event: AgentEvent) => void
) {
	const response = await fetch(getConsoleBlogBaseUrl() + '/ai/agent', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-Organization-Id': String(get(authOrganizationStore)?.id)
		},
		body: JSON.stringify({
			prompt,
			post_variant_id: postVariantId,
			conversation_id: conversationId
		}),
		credentials: 'same-origin'
	});

	if (!response.ok || !response.body) {
		let message = 'Could not reach the AI agent. Please try again.';
		try {
			const data = await response.json();
			if (data?.message) message = data.message;
		} catch {
			// ignore, keep the default message
		}
		throw new Error(message);
	}

	const reader = response.body.getReader();
	const decoder = new TextDecoder();
	let buffer = '';

	while (true) {
		const { done, value } = await reader.read();
		if (done) break;

		buffer += decoder.decode(value, { stream: true });

		// SSE events are separated by blank lines
		const parts = buffer.split('\n\n');
		buffer = parts.pop() ?? '';

		for (const part of parts) {
			const line = part.replace(/^data: /, '').trim();
			if (!line) continue;

			try {
				onEvent(JSON.parse(line) as AgentEvent);
			} catch {
				console.warn('Failed to parse agent event:', line);
				// ignore malformed SSE chunks
			}
		}
	}
}
