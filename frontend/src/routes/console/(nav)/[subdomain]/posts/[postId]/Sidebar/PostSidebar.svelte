<script lang="ts">
	import IconGear from '@hyvor/icons/IconGear';
	import IconSearchHeart from '@hyvor/icons/IconSearchHeart';
	import IconLink45deg from '@hyvor/icons/IconLink45deg';
	import IconRobot from '@hyvor/icons/IconRobot';

	import Popover from './Popover.svelte';
	import Settings from './Settings/Settings.svelte';
	import Seo from './Seo/Seo.svelte';
	import Ai from './Ai/Ai.svelte';
	import AgentReplyingTag from './Ai/AgentReplyingTag.svelte';
	import Links from './Links/Links.svelte';
	import LinksSidebarTag from './Links/LinksSidebarTag.svelte';
	import SeoScoreTag from './Seo/SeoScoreTag.svelte';
	import { variantSeoStore } from '../../seoStore';
	import { postSidebarStore } from '../../postStore';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();
</script>

<div class="post-sections">
	<Popover
		bind:show={
			() => $postSidebarStore === 'settings', (v) => ($postSidebarStore = v ? 'settings' : null)
		}
	>
		{#snippet trigger()}
			<IconGear size={13} class="icon" />
			{i18n.t('console.postEditor.sections.settings')}
		{/snippet}

		<Settings />
	</Popover>

	<span class="divider"></span>

	<Popover
		bind:show={() => $postSidebarStore === 'seo', (v) => ($postSidebarStore = v ? 'seo' : null)}
	>
		{#snippet trigger()}
			<IconSearchHeart size={13} class="icon" />
			{i18n.t('console.postEditor.sections.seo')}
			<SeoScoreTag score={$variantSeoStore.average} percentage />
		{/snippet}

		<Seo />
	</Popover>

	<span class="divider"></span>

	<Popover
		bind:show={() => $postSidebarStore === 'links', (v) => ($postSidebarStore = v ? 'links' : null)}
	>
		{#snippet trigger()}
			<IconLink45deg size={13} class="icon" />
			{i18n.t('console.postEditor.sections.links')}
			<LinksSidebarTag />
		{/snippet}

		<Links />
	</Popover>

	<span class="divider"></span>

	<Popover
		flush
		bind:show={() => $postSidebarStore === 'ai', (v) => ($postSidebarStore = v ? 'ai' : null)}
	>
		{#snippet trigger()}
			<IconRobot size={13} class="icon" />
			{i18n.t('console.postEditor.sections.agent')}
			<AgentReplyingTag />
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

	.post-sections :global(.icon) {
		opacity: 0.5;
		flex-shrink: 0;
	}
</style>
