import { writable } from 'svelte/store';
import type { AiConversation, AiMessage } from '../../../lib/types';

export interface AgentChatSession {
	conversation: AiConversation | null;
	messages: AiMessage[];
}

// in-memory cache of AgentChat's live state, keyed by conversation uuid (persisted
// conversations) or a synthetic draft key (not-yet-started conversations). Lets AgentChat
// survive being unmounted (e.g. the post editor's AI popover closing) and remounted (e.g.
// switching between conversations in a dropdown) without losing an in-progress conversation.
const sessions = new Map<string, AgentChatSession>();

export function draftSessionKey(postVariantId: number | null): string {
	return postVariantId !== null ? `post-${postVariantId}` : 'blog-new';
}

export function getAgentChatSession(key: string): AgentChatSession | undefined {
	return sessions.get(key);
}

export function setAgentChatSession(key: string, session: AgentChatSession) {
	sessions.set(key, session);
}

export function deleteAgentChatSession(key: string) {
	sessions.delete(key);
}

// remembers which conversation (by uuid, or null for "new chat") was last active for a given
// post variant, so re-opening the post editor's AI popover restores the same dropdown selection
// instead of always resetting to "New Chat"
const rememberedSelections = new Map<number, string | null>();

export function getRememberedConversationUuid(postVariantId: number): string | null | undefined {
	return rememberedSelections.get(postVariantId);
}

export function setRememberedConversationUuid(postVariantId: number, uuid: string | null) {
	rememberedSelections.set(postVariantId, uuid);
}

// real Svelte stores (not the plain caches above) since these need to reactively notify
// components that are mounted *while* a background stream started by a now-unmounted AgentChat
// instance finishes - a plain module-level value wouldn't trigger a re-render on its own.

// which chat sessions (by sessionKey - conversation uuid or draft key) currently have a live
// agent response streaming, so an AgentChat that gets unmounted and remounted mid-stream (e.g.
// the post editor's AI popover closing) shows the right loading state on reopen
export const replyingSessionsStore = writable<Set<string>>(new Set());

// which post variants currently have a live agent response streaming, independent of which
// conversation/session is open - used for the post editor's Sidebar "Agent" label
export const replyingPostVariantsStore = writable<Set<number>>(new Set());

export function setSessionReplying(sessionKey: string, replying: boolean) {
	replyingSessionsStore.update((current) => {
		const next = new Set(current);
		if (replying) {
			next.add(sessionKey);
		} else {
			next.delete(sessionKey);
		}
		return next;
	});
}

export function setPostVariantReplying(postVariantId: number | null, replying: boolean) {
	if (postVariantId === null) return;

	replyingPostVariantsStore.update((current) => {
		const next = new Set(current);
		if (replying) {
			next.add(postVariantId);
		} else {
			next.delete(postVariantId);
		}
		return next;
	});
}
