<script>
	import UpgradeRequired from '../../../../billing/UpgradeRequired.svelte';
	import LinkAnalysis from './LinkAnalysis.svelte';
</script>

<UpgradeRequired minPlan="growth" trialAllowed={true}>
	{#snippet upgradeText()}
		<div>
			Link Analysis is available on the <b>Growth plan</b> and above. Upgrade now to analyze links in
			your posts and say goodbye to broken links.
		</div>
	{/snippet}

	<LinkAnalysis />
</UpgradeRequired>
