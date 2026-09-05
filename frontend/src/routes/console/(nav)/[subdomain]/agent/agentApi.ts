import { get } from 'svelte/store';
import { authOrganizationStore } from '../../../lib/stores';
import { getConsoleBlogBaseUrl } from '../../../lib/consoleApi';
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
	eventId: number;
	postVariant: AiDocumentChangePostVariant;
	content: string;
	version: number; // version agent edited
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
