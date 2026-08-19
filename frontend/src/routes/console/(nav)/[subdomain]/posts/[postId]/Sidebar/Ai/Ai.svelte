<script lang="ts">
	import LicenseRequired from '../../../../../billing/LicenseRequired.svelte';
	import AgentChat from '../../../../agent/AgentChat.svelte';
	import { postEditor, postVariantStore } from '../../../postStore';
	import type { AgentPostVariant, DocumentChange } from '../../../../agent/agentApi';

	function applyDocumentChange(change: DocumentChange, _postVariant: AgentPostVariant) {
		// goes through the live collab editor rather than a direct save - setContent() dispatches
		// a normal transaction that the collab plugin turns into OT steps and submits/checkpoints
		// through the existing pipeline (see Editor.svelte), keeping document_version in sync
		$postEditor.setContent(change.content);
	}
</script>

<LicenseRequired licenseProperty="aiTokens">
	{#snippet upgradeText()}
		<div>
			AI chat is available on the <b>Growth plan</b> and above. Upgrade now to use GPT to generate and
			improve your content.
		</div>
	{/snippet}

	<AgentChat
		postVariantId={$postVariantStore.id}
		emptyMessage={`Ask the agent about this post, e.g. "Fix any typos" or "Add a short FAQ section at the end".`}
		disclaimer="The agent may suggest edits to this post."
		{applyDocumentChange}
	/>
</LicenseRequired>
