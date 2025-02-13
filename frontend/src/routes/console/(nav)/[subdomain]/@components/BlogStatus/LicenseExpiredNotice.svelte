<script>
	import IconClock from '@hyvor/icons/IconClock';
	import BlogStatus from './BlogStatus.svelte';
	import { goto } from '$app/navigation';
	import { consoleUrl } from '../../../../lib/consoleUrl';

	function handleUpgradeClick() {
		goto(consoleUrl('/billing'));
	}
</script>

<BlogStatus
	icon={IconClock}
	title="License Expired"
	description="Your license has expired. Please renew or upgrade to continue using the service."
	cta="Upgrade Now"
	on:click={handleUpgradeClick}
/>
