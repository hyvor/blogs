<script>
	import { NavLink } from '@hyvor/design/components';
	import { consoleUrl } from '../../lib/consoleUrl';
	import { page } from '$app/stores';
	import IconCoin from '@hyvor/icons/IconCoin';
</script>

<div class="wrap">
	<NavLink href={consoleUrl('/billing')} active={$page.url.pathname === `/console/billing`}>
		{#snippet start()}
			<IconCoin />
		{/snippet}
		Billing
	</NavLink>
</div>

<style>
	.wrap {
		padding: 15px 0;
	}
</style>
