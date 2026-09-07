<script>
	import IconClock from '@hyvor/icons/IconClock';
	import BlogStatus from './BlogStatus.svelte';
	import { goto } from '$app/navigation';
	import { consoleUrl } from '../../../../lib/consoleUrl';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	function handleUpgradeClick() {
		goto(consoleUrl('/billing'));
	}
</script>

<BlogStatus
	icon={IconClock}
	title={i18n.t('console.blogStatus.licenseExpired.title')}
	description={i18n.t('console.blogStatus.licenseExpired.description')}
	cta={i18n.t('console.blogStatus.licenseExpired.cta')}
	on:click={handleUpgradeClick}
/>
