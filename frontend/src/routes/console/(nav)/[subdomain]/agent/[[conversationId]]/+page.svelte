<script lang="ts">
	import { page } from '$app/state';
	import { goto } from '$app/navigation';
	import AgentChat from '../AgentChat.svelte';
	import { agentConversationsStore } from '../agentConversationsStore';
	import { consoleUrlWithBlog } from '../../../../lib/consoleUrl';
	import type { DocumentChange } from '../agentApi';

	async function applyDocumentChange(change: DocumentChange) {
		console.warn('Cannot yet save agent changes from the blog-level agent page', change);
	}

	let conversationId = $derived(
		page.params.conversationId ? Number(page.params.conversationId) : null
	);

	function handleConversationStarted(id: number, title: string | null) {
		agentConversationsStore.upsert(id, title);
		goto(consoleUrlWithBlog(`/agent/${id}`), {
			replaceState: true,
			noScroll: true,
			keepFocus: true
		});
	}
</script>

<div class="agent-page">
	<AgentChat
		postVariantId={null}
		{applyDocumentChange}
		initialConversationId={conversationId}
		onConversationStarted={handleConversationStarted}
	/>
</div>

<style>
	.agent-page {
		height: 100%;
		overflow: hidden;
	}
</style>
