<script lang="ts">
	import { page } from '$app/state';
	import { replaceState } from '$app/navigation';
	import AgentChat from '../AgentChat.svelte';
	import { agentConversationsStore } from '../agentConversationsStore';
	import { consoleUrlWithBlog } from '../../../../lib/consoleUrl';
	import type { AiConversation } from '../../../../lib/types';

	let conversationUuid = $derived(page.params.conversationUuid ?? null);

	function handleConversationCreated(conversation: AiConversation) {
		agentConversationsStore.upsert(conversation);
		agentConversationsStore.setActive(conversation.uuid);
		replaceState(consoleUrlWithBlog(`/agent/${conversation.uuid}`), {});
	}
</script>

{#key conversationUuid}
	<AgentChat {conversationUuid} onConversationCreated={handleConversationCreated} />
{/key}
