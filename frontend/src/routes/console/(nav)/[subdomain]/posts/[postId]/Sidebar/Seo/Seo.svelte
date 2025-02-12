<script>
	import LicenseRequired from '../../../../../billing/LicenseRequired.svelte';
	import SeoAnalysis from './SeoAnalysis.svelte';
</script>

<LicenseRequired license="analyses">
	{#snippet upgradeText()}
		<div>
			The SEO analyzer is available on the <b>Growth plan</b> and above. Upgrade now to up your
			SEO game.
		</div>
	{/snippet}

	<SeoAnalysis />
</LicenseRequired>
