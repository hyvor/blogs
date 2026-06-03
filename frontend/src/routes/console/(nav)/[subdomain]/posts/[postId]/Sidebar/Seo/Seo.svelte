<script>
	import LicenseRequired from '../../../../../billing/LicenseRequired.svelte';
	import SeoAnalysis from './SeoAnalysis.svelte';
</script>

<LicenseRequired licenseProperty="seoAnalysis">
	{#snippet upgradeText()}
		<div>
			The SEO analyzer is available on the <b>Starter plan</b> and above. Upgrade now to up your SEO game.
		</div>
	{/snippet}

	<SeoAnalysis />
</LicenseRequired>
