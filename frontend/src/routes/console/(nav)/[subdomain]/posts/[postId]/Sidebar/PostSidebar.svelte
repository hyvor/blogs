<script lang="ts">
	import Links from './Links/Links.svelte';
	import { TabNav, TabNavItem } from '@hyvor/design/components';
	import IconGear from '@hyvor/icons/IconGear';
	import IconLink45deg from '@hyvor/icons/IconLink45deg';
	import IconMagic from '@hyvor/icons/IconMagic';
	import IconSearchHeart from '@hyvor/icons/IconSearchHeart';

	import Settings from './Settings/Settings.svelte';
	import SeoScoreTag from './Seo/SeoScoreTag.svelte';
	import Seo from './Seo/Seo.svelte';
	import { variantSeoStore } from '../../seoStore';
	import Ai from './Ai/Ai.svelte';
	import { tab } from './sidebar';
	import LinksSidebarTag from './Links/LinksSidebarTag.svelte';
</script>

<div class="post-sidebar">
	<div class="body hds-box">
		<div class="nav">
			<TabNav>
				<TabNavItem
					name="settings"
					active={$tab === 'settings'}
					onclick={() => ($tab = 'settings')}
				>
					{#snippet start()}
						<IconGear />
					{/snippet}
					Settings
				</TabNavItem>

				<TabNavItem name="seo" active={$tab === 'seo'} onclick={() => ($tab = 'seo')}>
					{#snippet start()}
						<IconSearchHeart />
					{/snippet}
					SEO
					{#snippet end()}
						<SeoScoreTag score={$variantSeoStore.average} percentage />
					{/snippet}
				</TabNavItem>

				<TabNavItem name="links" active={$tab === 'links'} onclick={() => ($tab = 'links')}>
					{#snippet start()}
						<IconLink45deg />
					{/snippet}
					Links
					{#snippet end()}
						<LinksSidebarTag />
					{/snippet}
				</TabNavItem>

				<TabNavItem name="ai" active={$tab === 'ai'} onclick={() => ($tab = 'ai')}>
					{#snippet start()}
						<IconMagic />
					{/snippet}
					AI Agent
				</TabNavItem>
			</TabNav>
		</div>

		<div class="content" class:content-flush={$tab === 'ai'}>
			{#if $tab === 'settings'}
				<Settings />
			{:else if $tab === 'seo'}
				<Seo />
			{:else if $tab === 'links'}
				<Links />
			{:else if $tab === 'ai'}
				<Ai />
			{/if}
		</div>
	</div>
</div>

<style>
	.post-sidebar {
		height: 100%;
		display: flex;
		flex-direction: column;
	}

	.body {
		flex: 1;
		display: flex;
		flex-direction: column;
		min-height: 0;
	}

	.nav {
		padding: 15px 25px 0px;
		overflow: auto;
		font-size: 14px;
	}

	.content {
		padding: 15px 25px;
		flex: 1;
		min-height: 0;
		overflow: auto;
	}

	.content-flush {
		padding: 0;
		overflow: hidden;
		display: flex;
		flex-direction: column;
	}

	@media (max-width: 992px) {
		.post-sidebar {
			width: 100%;
			margin-top: 20px;
		}
	}
</style>
