import { get } from 'svelte/store';
import { authOrganizationStore } from '../../../lib/stores';
import consoleApi, { getConsoleBlogBaseUrl } from '../../../lib/consoleApi';
import type { PostVariant } from '../../../lib/types';

export const DEFAULT_CONTENT_JSON = '{"type":"doc","content":[{"type":"paragraph","content":[]}]}';

export type AgentEvent =
	| { type: 'conversation_started'; conversation_id: number; title: string | null }
	| ({ type: 'post_variant' } & Record<string, unknown>)
	| { type: 'thinking_started' }
	| { type: 'thinking'; content: string }
	| { type: 'thinking_done' }
	| { type: 'tool_call'; tool: string }
	| { type: 'tool_result'; tool: string }
	| { type: 'post_variant_read'; post_variant_id: number }
	| {
			type: 'post_variant_edit_suggested';
			post_variant_id: number;
			operation: string;
			arguments: Record<string, unknown>;
	  }
	| ({ type: 'get_tags' } & Record<string, unknown>)
	| ({ type: 'get_authors' } & Record<string, unknown>)
	| ({ type: 'get_post_variants' } & Record<string, unknown>)
	| { type: 'text'; content: string }
	| { type: 'document_change'; post_variant_id: number; content: string }
	| { type: 'error'; message: string }
	| { type: 'done' };

export type AgentBlock =
	| { type: 'thinking'; content: string; done: boolean }
	| { type: 'tool'; name: string; status: 'running' | 'done' }
	| { type: 'text'; content: string }
	| { type: 'variant_activity'; postVariantId: number; reads: number; edits: number };

export interface DocumentChange {
	postVariantId: number;
	content: string;
}

export interface AgentConversationListItem {
	id: number;
	created_at: number;
	updated_at: number;
	title: string | null;
}

export interface AgentConversationsResponse {
	conversations: AgentConversationListItem[];
	has_more: boolean;
}

export type AgentTurn =
	| { role: 'user'; content: string }
	| {
			role: 'assistant';
			events: AgentEvent[];
			model: string | null;
			input_tokens: number | null;
			output_tokens: number | null;
			total_tokens: number | null;
	  };

export interface AgentConversationDetail {
	id: number;
	title: string | null;
	created_at: number;
	updated_at: number;
	turns: AgentTurn[];
}

// Persists a document change directly (no collab session involved) - used by the whole-blog
// agent page, which never mounts a live post editor for the post it just edited. The sidebar
// agent instead applies changes through the live editor's collab pipeline (see Editor.svelte's
// setContent) so the document_version counter stays in sync - this PATCH path would desync it.
export function saveAgentDocumentChange(postId: number, languageId: number, content: string) {
	return consoleApi.patch<PostVariant>({
		endpoint: `/post/${postId}/variant`,
		data: {
			language_id: languageId,
			content_unsaved: content
		}
	});
}

export function getAgentConversations(limit = 25, offset = 0) {
	return consoleApi.get<AgentConversationsResponse>({
		endpoint: '/ai/conversations',
		data: { limit, offset }
	});
}

export function getAgentConversation(conversationId: number) {
	return consoleApi.get<AgentConversationDetail>({
		endpoint: `/ai/conversation/${conversationId}`
	});
}

export function deleteAgentConversation(conversationId: number) {
	return consoleApi.delete<void>({
		endpoint: `/ai/conversation/${conversationId}`
	});
}

function trackVariantActivity(blocks: AgentBlock[], postVariantId: number, kind: 'read' | 'edit') {
	let activity = blocks.find(
		(b): b is Extract<AgentBlock, { type: 'variant_activity' }> =>
			b.type === 'variant_activity' && b.postVariantId === postVariantId
	);

	if (!activity) {
		activity = { type: 'variant_activity', postVariantId, reads: 0, edits: 0 };
		blocks.push(activity);
	}

	if (kind === 'read') {
		activity.reads += 1;
	} else {
		activity.edits += 1;
	}
}

/**
 * Folds a single SSE event from the agent stream into the turn's block list,
 * merging consecutive deltas of the same kind (thinking/text) into one block, and
 * grouping post-variant read/edit activity by post variant into one block.
 */
export function applyAgentEvent(blocks: AgentBlock[], event: AgentEvent) {
	const last = blocks[blocks.length - 1];

	switch (event.type) {
		case 'thinking':
			if (last?.type === 'thinking' && !last.done) {
				last.content += event.content;
			} else {
				blocks.push({ type: 'thinking', content: event.content, done: false });
			}
			break;

		case 'thinking_done':
			if (last?.type === 'thinking') {
				last.done = true;
			}
			break;

		case 'tool_call':
			blocks.push({ type: 'tool', name: event.tool, status: 'running' });
			break;

		case 'tool_result': {
			const tool = [...blocks]
				.reverse()
				.find(
					(b): b is Extract<AgentBlock, { type: 'tool' }> =>
						b.type === 'tool' && b.name === event.tool && b.status === 'running'
				);
			if (tool) {
				tool.status = 'done';
			}
			break;
		}

		case 'post_variant_read':
			trackVariantActivity(blocks, event.post_variant_id, 'read');
			break;

		case 'post_variant_edit_suggested':
			trackVariantActivity(blocks, event.post_variant_id, 'edit');
			break;

		case 'get_tags':
		case 'get_authors':
		case 'get_post_variants':
		case 'post_variant':
			break;

		case 'text':
			if (last?.type === 'text') {
				last.content += event.content;
			} else {
				blocks.push({ type: 'text', content: event.content });
			}
			break;
	}
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
