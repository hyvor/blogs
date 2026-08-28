<script lang="ts">
	import IconGear from '@hyvor/icons/IconGear';
	import IconSearchHeart from '@hyvor/icons/IconSearchHeart';
	import IconLink45deg from '@hyvor/icons/IconLink45deg';
	import IconRobot from '@hyvor/icons/IconRobot';

	import Popover from './Popover.svelte';
	import Settings from './Settings/Settings.svelte';
	import Seo from './Seo/Seo.svelte';
	import Ai from './Ai/Ai.svelte';
	import Links from './Links/Links.svelte';
	import LinksSidebarTag from './Links/LinksSidebarTag.svelte';
	import SeoScoreTag from './Seo/SeoScoreTag.svelte';
	import { variantSeoStore } from '../../seoStore';

	type Section = 'settings' | 'seo' | 'links' | 'ai';

	let openSection: Section | null = $state(null);
</script>

<div class="post-sections">
	<Popover
		bind:show={() => openSection === 'settings', (v) => (openSection = v ? 'settings' : null)}
	>
		{#snippet trigger()}
			<IconGear size={13} class="icon" />
			Settings
		{/snippet}

		<Settings />
	</Popover>

	<span class="divider"></span>

	<Popover bind:show={() => openSection === 'seo', (v) => (openSection = v ? 'seo' : null)}>
		{#snippet trigger()}
			<IconSearchHeart size={13} class="icon" />
			SEO
			<SeoScoreTag score={$variantSeoStore.average} percentage />
		{/snippet}

		<Seo />
	</Popover>

	<span class="divider"></span>

	<Popover bind:show={() => openSection === 'links', (v) => (openSection = v ? 'links' : null)}>
		{#snippet trigger()}
			<IconLink45deg size={13} class="icon" />
			Links
			<LinksSidebarTag />
		{/snippet}

		<Links />
	</Popover>

	<span class="divider"></span>

	<Popover flush bind:show={() => openSection === 'ai', (v) => (openSection = v ? 'ai' : null)}>
		{#snippet trigger()}
			<IconRobot size={13} class="icon" />
			Agent
		{/snippet}

		<Ai />
	</Popover>
</div>

<style>
	.post-sections {
		height: 100%;
		display: flex;
		align-items: center;
		gap: 4px;
	}

	.divider {
		width: 1px;
		height: 16px;
		background-color: var(--border);
	}

	:global(.icon) {
		opacity: 0.5;
		flex-shrink: 0;
	}
</style>
