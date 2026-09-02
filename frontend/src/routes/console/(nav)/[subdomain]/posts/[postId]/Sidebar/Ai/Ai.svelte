<script lang="ts">
	import LicenseRequired from '../../../../../billing/LicenseRequired.svelte';
	import AgentChat from '../../../../agent/AgentChat.svelte';
	import { postEditor, postVariantStore } from '../../../postStore';
	import type { DocumentChange } from '../../../../agent/agentApi';

	function applyDocumentChange(change: DocumentChange) {
		// only the currently open post
		if (change.postVariantId === $postVariantStore.id) {
			$postEditor.setContent(change.content);
		}
	}
</script>

<LicenseRequired licenseProperty="aiCost">
	{#snippet upgradeText()}
		<div>
			AI agent is available on the <b>Starter Plan</b> and above. Upgrade your plan to unlock this feature.
		</div>
	{/snippet}

	<!-- emptyMessage={`Ask the agent about this post, e.g. "Fix any typos" or "Add a short FAQ section at the end".`}
		{applyDocumentChange} -->
	<AgentChat />
</LicenseRequired>
