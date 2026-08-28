<script lang="ts">
	import IconRobot from '@hyvor/icons/IconRobot';
	import AgentChat from './AgentChat.svelte';
	import type { DocumentChange } from './agentApi';

	// document_change events only carry a post variant ID + the new content, not the parent
	// post's ID - so there's no endpoint we can call to persist it from here yet (unlike the
	// post editor's sidebar, this page never loads the post, so there's no live editor either).
	// TODO: apply directly once post variant metadata (post_id, language_id) is available here.
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
		display: flex;
		flex-direction: column;
		overflow: hidden;
	}
</style>
