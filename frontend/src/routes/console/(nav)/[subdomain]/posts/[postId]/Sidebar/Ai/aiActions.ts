import { getConsoleBlogBaseUrl } from '../../../../../../lib/consoleApi';
import { get } from 'svelte/store';
import { authOrganizationStore } from '../../../../../../lib/stores';

export type AgentEvent =
	| { type: 'thinking'; content: string }
	| { type: 'thinking_done' }
	| { type: 'tool_call'; tool: string }
	| { type: 'tool_result'; status: string }
	| { type: 'text'; content: string };

export type AgentBlock =
	| { type: 'thinking'; content: string; done: boolean }
	| { type: 'tool'; name: string; status: 'running' | 'done' }
	| { type: 'text'; content: string };

export interface AgentTurn {
	id: number;
	prompt: string;
	blocks: AgentBlock[];
	status: 'streaming' | 'done' | 'error';
	error: string | null;
}

/**
 * Folds a single SSE event from the agent stream into the turn's block list,
 * merging consecutive deltas of the same kind (thinking/text) into one block.
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

		case 'tool_result':
			for (let i = blocks.length - 1; i >= 0; i--) {
				const block = blocks[i];
				if (block && block.type === 'tool' && block.status === 'running') {
					block.status = 'done';
					break;
				}
			}
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

export async function callAgent(onEvent: (event: AgentEvent) => void) {
	const response = await fetch(getConsoleBlogBaseUrl() + '/ai/agent', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-Organization-Id': String(get(authOrganizationStore)?.id)
		},
		body: JSON.stringify({}),
		credentials: 'same-origin'
	});

	if (!response.ok || !response.body) {
		throw new Error('Could not reach the AI agent. Please try again.');
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
