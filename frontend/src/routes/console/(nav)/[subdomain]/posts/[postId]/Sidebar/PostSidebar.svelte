<script lang="ts">
	import { Dropdown } from '@hyvor/design/components';
	import IconGear from '@hyvor/icons/IconGear';
	import IconLink45deg from '@hyvor/icons/IconLink45deg';
	import IconSearchHeart from '@hyvor/icons/IconSearchHeart';

	import Settings from './Settings/Settings.svelte';
	import Seo from './Seo/Seo.svelte';
	import Ai from './Ai/Ai.svelte';
	import Links from './Links/Links.svelte';
	import LinksSidebarTag from './Links/LinksSidebarTag.svelte';
	import IconRobot from '@hyvor/icons/IconRobot';
	import SeoScoreTag from './Seo/SeoScoreTag.svelte';
	import { variantSeoStore } from '../../seoStore';

	type Section = 'settings' | 'seo' | 'links' | 'ai';

	let openSection: Section | null = $state(null);
</script>

<div class="post-sections">
	<Dropdown
		align="center"
		width={380}
		contentPadding={0}
		bind:show={() => openSection === 'settings', (v) => (openSection = v ? 'settings' : null)}
	>
		{#snippet trigger()}
			<button class:active={openSection === 'settings'}>
				<IconGear size={14} />
				Settings
			</button>
		{/snippet}

		{#snippet content()}
			<div class="section-popover">
				<span class="pointer"></span>
				<div class="section-body">
					<Settings />
				</div>
			</div>
		{/snippet}
	</Dropdown>

	<Dropdown
		align="center"
		width={340}
		contentPadding={0}
		bind:show={() => openSection === 'seo', (v) => (openSection = v ? 'seo' : null)}
	>
		{#snippet trigger()}
			<button class:active={openSection === 'seo'}>
				<IconSearchHeart size={14} />
				SEO
				<SeoScoreTag score={$variantSeoStore.average} percentage />
			</button>
		{/snippet}

		{#snippet content()}
			<div class="section-popover">
				<span class="pointer"></span>
				<div class="section-body">
					<Seo />
				</div>
			</div>
		{/snippet}
	</Dropdown>

	<Dropdown
		align="center"
		width={340}
		contentPadding={0}
		bind:show={() => openSection === 'links', (v) => (openSection = v ? 'links' : null)}
	>
		{#snippet trigger()}
			<button class:active={openSection === 'links'}>
				<IconLink45deg size={14} />
				Links
			</button>
		{/snippet}

		{#snippet content()}
			<div class="section-popover">
				<span class="pointer"></span>
				<div class="section-body">
					<Links />
				</div>
			</div>
		{/snippet}
	</Dropdown>

	<Dropdown
		align="center"
		width={420}
		contentPadding={0}
		bind:show={() => openSection === 'ai', (v) => (openSection = v ? 'ai' : null)}
	>
		{#snippet trigger()}
			<button class:active={openSection === 'ai'}>
				<IconRobot size={14} />
				AI Agent
			</button>
		{/snippet}

		{#snippet content()}
			<div class="section-popover">
				<span class="pointer"></span>
				<div class="section-body flush">
					<Ai />
				</div>
			</div>
		{/snippet}
	</Dropdown>
</div>

<style>
	.post-sections {
		display: flex;
		align-items: center;
		gap: 15px;
	}

	.section-popover {
		position: relative;
	}

	.pointer {
		position: absolute;
		top: -6px;
		left: 50%;
		width: 12px;
		height: 12px;
		transform: translateX(-50%) rotate(45deg);
		background-color: var(--box-background);
		box-shadow: -1px -1px 1px rgba(0, 0, 0, 0.04);
		border-radius: 2px;
	}

	.section-body {
		max-height: 70vh;
		overflow: auto;
		padding: 15px 20px;
		font-size: 14px;
	}

	.section-body.flush {
		height: 520px;
		max-height: 75vh;
		padding: 0;
		overflow: hidden;
		display: flex;
		flex-direction: column;
	}

	button {
		font-size: 14px;
		display: inline-flex;
		gap: 4px;
		align-items: center;
		transition: color 0.2s ease;
	}

	button:hover {
		color: var(--accent);
	}

	button.active {
		color: var(--accent);
	}
</style>
