<script lang="ts">
	import { page } from '$app/state';
	// import { goto } from '$app/navigation';
	// import AgentChat from '../AgentChat.svelte';
	// import { agentConversationsStore } from '../agentConversationsStore';
	// import { consoleUrlWithBlog } from '../../../../lib/consoleUrl';
	// import type { DocumentChange } from '../agentApi';
	import { Loader, toast } from '@hyvor/design/components';
	import ConversationView from '../ConversationView.svelte';
	import IdleMessage from '../IdleMessage.svelte';
	import { getAiConversation, type AiConversationDetail } from '../aiConversationApi';

	// async function applyDocumentChange(change: DocumentChange) {
	// 	console.warn('Cannot yet save agent changes from the blog-level agent page', change);
	// }

	let conversationId = $derived(
		page.params.conversationId ? Number(page.params.conversationId) : null
	);

	// function handleConversationStarted(id: number, title: string | null) {
	// 	agentConversationsStore.upsert(id, title);
	// 	goto(consoleUrlWithBlog(`/agent/${id}`), {
	// 		replaceState: true,
	// 		noScroll: true,
	// 		keepFocus: true
	// 	});
	// }

	let loading = $state(false);
	let detail: AiConversationDetail | null = $state(null);

	async function loadConversation(id: number) {
		loading = true;
		detail = null;

		try {
			detail = await getAiConversation(id);
		} catch {
			toast.error('Failed to load this conversation.');
		} finally {
			loading = false;
		}
	}

	$effect(() => {
		const id = conversationId;
		if (id === null) {
			detail = null;
		} else {
			loadConversation(id);
		}
	});
</script>

<div class="agent-page">
	{#if loading}
		<div class="state-message"><Loader /></div>
	{:else if detail}
		<ConversationView conversation={detail.conversation} messages={detail.messages} />
	{:else if conversationId === null}
		<IdleMessage />
	{:else}
		<div class="state-message">Conversation not found.</div>
	{/if}

	<!--
	<AgentChat
		postVariantId={null}
		{applyDocumentChange}
		initialConversationId={conversationId}
		onConversationStarted={handleConversationStarted}
	/>
	-->
</div>

<style>
	.agent-page {
		height: 100%;
		overflow: hidden;
	}

	.state-message {
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		color: var(--text-light);
	}
</style>
