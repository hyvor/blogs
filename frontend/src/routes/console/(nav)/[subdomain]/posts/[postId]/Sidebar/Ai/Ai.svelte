<script lang="ts">
	import LicenseRequired from '../../../../../billing/LicenseRequired.svelte';
	import AgentChat from '../../../../agent/AgentChat.svelte';
	import { postVariantStore } from '../../../postStore';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;
</script>

<LicenseRequired licenseProperty="aiCost">
	{#snippet upgradeText()}
		<div>
			AI agent is available on the <b>Starter Plan</b> and above. Upgrade your plan to unlock this feature.
		</div>
	{/snippet}

	<AgentChat postVariantId={$postVariantStore.id} />
</LicenseRequired>
