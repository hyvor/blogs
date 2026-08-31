<script>
	import { NavLink } from '@hyvor/design/components';
	import { consoleUrl } from '../../lib/consoleUrl';
	import { page } from '$app/state';
	import IconCurrencyEuro from '@hyvor/icons/IconCurrencyEuro';
	import { canAccessBilling } from '../../lib/orgPermission';
	import { getI18n } from '../../lib/i18n';

	const i18n = getI18n();
</script>

<div class="wrap">
	<NavLink
		href={consoleUrl('/billing')}
		active={page.url.pathname === `/console/billing`}
		disabled={!canAccessBilling()}
	>
		{#snippet start()}
			<IconCurrencyEuro />
		{/snippet}
		{i18n.t('console.nav.billing')}
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
