import consoleApi from '../../../lib/consoleApi';
import type { PostStatus } from '../../../lib/types';

export type AiMessageRole = 'user' | 'assistant';

export type AiMessageEventType = 'text' | 'thinking' | 'query' | 'document_change';

export type AiMessageEventDocumentChangeStatus = 'pending' | 'reviewed';

export interface AiMessageEvent {
	type: AiMessageEventType;
	// text: the text chunk. thinking: the thinking summary.
	content?: string | null;
	// query only - name of the query tool (e.g. get_tags, get_authors, get_post_variants)
	tool_name?: string | null;
	// query only
	tool_input?: unknown;
	// query only
	tool_output?: unknown;
	// document_change only
	post_variant_id?: number | null;
	// document_change only - the final document content, as a JSON string
	document_content?: string | null;
	// document_change only
	document_change_status?: AiMessageEventDocumentChangeStatus | null;
	// document_change only
	document_change_ops_count?: number | null;
	// document_change only - the post variant's content_unsaved_version when the agent first
	// fetched it, so the frontend can tell if the post has since changed
	post_variant_version?: number | null;
}

export interface AiMessageFrontEndEvent extends AiMessageEvent {
	// whether an event is pending
	// e.g., thinking, querying, document_changes in progress
	pending?: boolean;
}

export interface AiMessage {
	id: number;
	created_at: number;
	role: AiMessageRole;
	content: string;
	events: AiMessageEvent[];
}

export interface AiConversation {
	id: number;
	created_at: number;
	title: string;
}

// summary of a post variant referenced by a document_change event somewhere in the conversation
export interface AiConversationPostVariant {
	id: number;
	title: string | null;
	status: PostStatus;
	published_at: number | null;
}

export interface AiConversationDetail {
	conversation: AiConversation;
	messages: AiMessage[];
	post_variants: AiConversationPostVariant[];
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
