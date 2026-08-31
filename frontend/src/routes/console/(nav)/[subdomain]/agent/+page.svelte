<script lang="ts">
	import AgentChat from './AgentChat.svelte';
	import type { DocumentChange } from './agentApi';
	async function applyDocumentChange(change: DocumentChange) {
		console.warn('Cannot yet save agent changes from the blog-level agent page', change);
	}
</script>

<div class="agent-page hds-box">
	<AgentChat postVariantId={null} {applyDocumentChange} />
</div>

<style>
	.agent-page {
		height: 100%;
		overflow: hidden;
	}
</style>
