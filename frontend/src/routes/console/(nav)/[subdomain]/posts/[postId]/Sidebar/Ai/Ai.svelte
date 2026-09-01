<script lang="ts">
	import LicenseRequired from '../../../../../billing/LicenseRequired.svelte';
	import AgentChat from '../../../../agent/AgentChat.svelte';
	import { postEditor, postVariantStore } from '../../../postStore';
	import type { DocumentChange } from '../../../../agent/agentApi';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

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
			<T key="console.postEditor.agent.upgradeText" params={{ strong: { element: 'strong' } }} />
		</div>
	{/snippet}

	<AgentChat
		postVariantId={$postVariantStore.id}
		emptyMessage={i18n.t('console.postEditor.agent.emptyMessage')}
		{applyDocumentChange}
	/>
</LicenseRequired>
