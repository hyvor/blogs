<script>
	import LicenseRequired from '../../../../../billing/LicenseRequired.svelte';
	import LinkAnalysis from './LinkAnalysis.svelte';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;
</script>

<LicenseRequired licenseProperty="linkAnalysis">
	{#snippet upgradeText()}
		<div>
			<T key="console.postEditor.links.upgradeText" params={{ strong: { element: 'strong' } }} />
		</div>
	{/snippet}

	<LinkAnalysis />
</LicenseRequired>
