<script>
	import { NavLink } from '@hyvor/design/components';
	import { consoleUrl } from '../../lib/consoleUrl';
	import { page } from '$app/stores';
	import IconCurrencyEuro from '@hyvor/icons/IconCurrencyEuro';
	import { onMount } from 'svelte';
</script>

<div class="wrap">
	<NavLink href={consoleUrl('/billing')} active={$page.url.pathname === `/console/billing`}>
		{#snippet start()}
			<IconCurrencyEuro />
		{/snippet}
		Billing
	</NavLink>
</div>

<style>
	.wrap {
		padding: 15px 0;
	}
	.wrap :global(a.active) {
		background-color: var(--accent-light-mid);
	}
</style>
