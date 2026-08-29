<script lang="ts">
	import LicenseRequired from '../../../../../billing/LicenseRequired.svelte';
	import AgentChat from '../../../../agent/AgentChat.svelte';
	import { postEditor, postVariantStore } from '../../../postStore';
	import type { DocumentChange } from '../../../../agent/agentApi';

	function applyDocumentChange(change: DocumentChange) {
		// only the currently open post has a live editor session to push content into - the
		// agent can now suggest edits to other posts too, but those aren't open here to apply to
		if (change.postVariantId === $postVariantStore.id) {
			$postEditor.setContent(change.content);
		}
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
		{applyDocumentChange}
	/>
</LicenseRequired>
