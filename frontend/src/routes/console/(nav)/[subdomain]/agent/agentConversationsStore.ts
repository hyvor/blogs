import { get, writable } from 'svelte/store';
import {
	deleteAiConversation,
	getAiConversations,
} from './aiConversationApi';
import type { AiConversation } from '../../../lib/types';

const PAGE_SIZE = 25;

interface AgentConversationsState {
	conversations: AiConversation[];
	hasMore: boolean;
	loading: boolean;
	loadingMore: boolean;
	loaded: boolean;
	activeId: string | null;
}

function createAgentConversationsStore() {
	const store = writable<AgentConversationsState>({
		conversations: [],
		hasMore: false,
		loading: false,
		loadingMore: false,
		loaded: false,
		activeId: null
	});
	const { subscribe, update } = store;

	async function load() {
		update((s) => ({ ...s, loading: true }));

		try {
			const res = await getAiConversations(PAGE_SIZE, 0);
			update((s) => ({
				...s,
				conversations: res,
				hasMore: res.length === PAGE_SIZE,
				loading: false,
				loadingMore: false,
				loaded: true
			}));
		} catch {
			update((s) => ({ ...s, loading: false, loaded: true }));
		}
	}

	async function loadMore() {
		const current = get(store);
		if (current.loadingMore || !current.hasMore) return;

		update((s) => ({ ...s, loadingMore: true }));

		try {
			const res = await getAiConversations(PAGE_SIZE, current.conversations.length);
			update((s) => ({
				...s,
				conversations: [...s.conversations, ...res],
				hasMore: res.length === PAGE_SIZE,
				loadingMore: false
			}));
		} catch {
			update((s) => ({ ...s, loadingMore: false }));
		}
	}

	// prepends a freshly started conversation, or moves it to the top if it's already listed
	function upsert(conversation: { id: number; uuid: string; title: string | null }) {
		const now = Math.floor(Date.now() / 1000);
		update((s) => {
			const existing = s.conversations.find((c) => c.uuid === conversation.uuid);
			const conversations = s.conversations.filter((c) => c.uuid !== conversation.uuid);
			conversations.unshift({
				id: conversation.id,
				uuid: conversation.uuid,
				title: conversation.title ?? existing?.title ?? '',
				created_at: existing?.created_at ?? now,
				updated_at: now
			});
			return { ...s, conversations };
		});
	}

	async function remove(id: number) {
		await deleteAiConversation(id);
		update((s) => ({ ...s, conversations: s.conversations.filter((c) => c.id !== id) }));
	}

	function setActive(uuid: string | null) {
		update((s) => (s.activeId === uuid ? s : { ...s, activeId: uuid }));
	}

	return { subscribe, load, loadMore, upsert, remove, setActive };
}

export const agentConversationsStore = createAgentConversationsStore();
