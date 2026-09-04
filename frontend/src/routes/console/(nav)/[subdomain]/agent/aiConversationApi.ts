import consoleApi from '../../../lib/consoleApi';
import type { AiConversation, AiMessage, PostStatus } from '../../../lib/types';

export type AiMessageRole = 'user' | 'assistant';

export type AiMessageEventType = 'text' | 'thinking' | 'query' | 'document_change' | 'error';

export type AiMessageEventDocumentChangeStatus = 'pending' | 'reviewed';

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

export function getAiConversation(conversationUuid: string) {
	return consoleApi.get<AiConversationDetail>({
		endpoint: `/ai/conversation/${conversationUuid}`
	});
}

export function deleteAiConversation(conversationId: number) {
	return consoleApi.delete<void>({
		endpoint: `/ai/conversation/${conversationId}`
	});
}