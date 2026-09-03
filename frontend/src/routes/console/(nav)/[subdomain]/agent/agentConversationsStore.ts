import { get, writable } from 'svelte/store';
import {
	deleteAgentConversation,
	getAgentConversations,
	type AgentConversationListItem
} from './agentApi';

const PAGE_SIZE = 25;

interface AgentConversationsState {
	conversations: AgentConversationListItem[];
	hasMore: boolean;
	loading: boolean;
	loadingMore: boolean;
	loaded: boolean;
	activeId: number | null;
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
			const res = await getAgentConversations(PAGE_SIZE, 0);
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
			const res = await getAgentConversations(PAGE_SIZE, current.conversations.length);
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
	function upsert(id: number, title: string | null) {
		const now = Math.floor(Date.now() / 1000);
		update((s) => {
			const existing = s.conversations.find((c) => c.id === id);
			const conversations = s.conversations.filter((c) => c.id !== id);
			conversations.unshift({
				id,
				title: title ?? existing?.title ?? null,
				created_at: existing?.created_at ?? now,
				updated_at: now
			});
			return { ...s, conversations };
		});
	}

	async function remove(id: number) {
		await deleteAgentConversation(id);
		update((s) => ({ ...s, conversations: s.conversations.filter((c) => c.id !== id) }));
	}

	function setActive(id: number | null) {
		update((s) => (s.activeId === id ? s : { ...s, activeId: id }));
	}

	return { subscribe, load, loadMore, upsert, remove, setActive };
}

export const agentConversationsStore = createAgentConversationsStore();
