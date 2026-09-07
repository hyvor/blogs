<script>
	import LicenseRequired from '../../../../../billing/LicenseRequired.svelte';
	import SeoAnalysis from './SeoAnalysis.svelte';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;
</script>

<LicenseRequired licenseProperty="seoAnalysis">
	{#snippet upgradeText()}
		<div>
			<T key="console.postEditor.seo.upgradeText" params={{ strong: { element: 'strong' } }} />
		</div>
	{/snippet}

	<SeoAnalysis />
</LicenseRequired>
