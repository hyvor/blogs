<script lang="ts">
	import Links from './Links/Links.svelte';
	import { Loader, TabNav, TabNavItem } from '@hyvor/design/components';
	import IconCardChecklist from '@hyvor/icons/IconCardChecklist';
	import IconGear from '@hyvor/icons/IconGear';
	import IconLink45deg from '@hyvor/icons/IconLink45deg';
	import Settings from './Settings.svelte';
	import { getStats } from './linkAnalysisActions';
	import Overview from './Overview/Overview.svelte';
	import LicenseRequired from '../../../billing/LicenseRequired.svelte';

	let tab: 'overview' | 'links' | 'settings' = $state('overview');

	const statsPromise = getStats();
</script>

<div class="link-analysis hds-box">
	<LicenseRequired licenseProperty="analyses">
		{#snippet upgradeText()}
			<div>
				Link Analysis is available on the <b>Growth plan</b> and above. Upgrade now to automatically analyze
				all links in your blog and receive email reports.
			</div>
		{/snippet}

		<TabNav>
			<TabNavItem name="overview" active={tab === 'overview'} onclick={() => (tab = 'overview')}>
				{#snippet start()}
					<IconCardChecklist />
				{/snippet}
				Overview
			</TabNavItem>

			<TabNavItem name="links" active={tab === 'links'} onclick={() => (tab = 'links')}>
				{#snippet start()}
					<IconLink45deg />
				{/snippet}
				Links
			</TabNavItem>

			<TabNavItem name="settings" active={tab === 'settings'} onclick={() => (tab = 'settings')}>
				{#snippet start()}
					<IconGear />
				{/snippet}
				Settings
			</TabNavItem>
		</TabNav>

		<div class="content">
			{#await statsPromise}
				<Loader block padding={60} />
			{:then stats}
				{#if tab === 'overview'}
					<Overview {stats} on:links={() => (tab = 'links')} />
				{:else if tab === 'links'}
					<Links {stats} />
				{:else if tab === 'settings'}
					<Settings />
				{/if}
			{/await}
		</div>
	</LicenseRequired>
</div>

<style>
	.link-analysis {
		height: 100%;
		overflow: auto;
		padding: 20px 30px;
	}
	.content {
		margin-top: 25px;
	}
</style>
