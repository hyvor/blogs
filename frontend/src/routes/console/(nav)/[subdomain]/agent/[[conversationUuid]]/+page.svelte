<script lang="ts">
	import { page } from '$app/state';
	import AgentChat from '../AgentChat.svelte';

	let conversationUuid = $derived(page.params.conversationUuid ?? null);
</script>

{#key conversationUuid}
	<AgentChat {conversationUuid} />
{/key}
