import consoleApi from '../../../lib/consoleApi';

export type AiMessageRole = 'user' | 'assistant';

export interface AiMessageThinking {
	id: number;
	created_at: number;
	summary: string;
}

export interface AiMessageToolCall {
	id: number;
	created_at: number;
	name: string;
	arguments: Record<string, unknown>;
}

export interface AiMessage {
	id: number;
	created_at: number;
	role: AiMessageRole;
	content: string;
	thinking: AiMessageThinking[];
	tool_calls: AiMessageToolCall[];
}

export interface AiConversation {
	id: number;
	created_at: number;
	title: string;
}

export interface AiConversationDetail {
	conversation: AiConversation;
	messages: AiMessage[];
}

export function getAiConversations(limit = 25, offset = 0) {
	return consoleApi.get<AiConversation[]>({
		endpoint: '/ai/conversations',
		data: { limit, offset }
	});
}

export function getAiConversation(conversationId: number) {
	return consoleApi.get<AiConversationDetail>({
		endpoint: `/ai/conversation/${conversationId}`
	});
}
