<script lang="ts">
	import { page } from '$app/state';
	import AgentChat from '../AgentChat.svelte';

	let conversationId = $derived(
		page.params.conversationId ? Number(page.params.conversationId) : null
	);
</script>

{#key conversationId}
	<AgentChat {conversationId} />
{/key}
