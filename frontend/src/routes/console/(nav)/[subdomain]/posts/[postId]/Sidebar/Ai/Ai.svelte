<script>
	import UpgradeRequired from '../../../../billing/UpgradeRequired.svelte';
	import AiChat from './AiChat.svelte';
</script>

<UpgradeRequired minPlan="growth" trialAllowed={true}>
	{#snippet upgradeText()}
		<div>
			AI chat is available on the <b>Growth plan</b> and above. Upgrade now to use GPT to generate and
			improve your content.
		</div>
	{/snippet}

	<AiChat />
</UpgradeRequired>
