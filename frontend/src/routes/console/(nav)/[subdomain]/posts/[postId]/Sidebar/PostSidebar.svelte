<script lang="ts">
	import { clickOutside } from '@hyvor/design/components';

	import Settings from './Settings/Settings.svelte';
	import Seo from './Seo/Seo.svelte';
	import Ai from './Ai/Ai.svelte';
	import Links from './Links/Links.svelte';
	import LinksSidebarTag from './Links/LinksSidebarTag.svelte';
	import SeoScoreTag from './Seo/SeoScoreTag.svelte';
	import { variantSeoStore } from '../../seoStore';

	type Section = 'settings' | 'seo' | 'links' | 'ai';

	let openSection: Section | null = $state(null);

	function toggle(section: Section) {
		openSection = openSection === section ? null : section;
	}

	function closeIfOpen(section: Section) {
		if (openSection === section) {
			openSection = null;
		}
	}
</script>

<div class="post-sections">
	<div class="section">
		<button class:active={openSection === 'settings'} onclick={() => toggle('settings')}>
			Settings
		</button>

		{#if openSection === 'settings'}
			<div class="popover" use:clickOutside={{ callback: () => closeIfOpen('settings') }}>
				<span class="pointer"></span>
				<div class="popover-body">
					<Settings />
				</div>
			</div>
		{/if}
	</div>

	<div class="section">
		<button class:active={openSection === 'seo'} onclick={() => toggle('seo')}>
			SEO
			<SeoScoreTag score={$variantSeoStore.average} percentage />
		</button>

		{#if openSection === 'seo'}
			<div class="popover" use:clickOutside={{ callback: () => closeIfOpen('seo') }}>
				<span class="pointer"></span>
				<div class="popover-body">
					<Seo />
				</div>
			</div>
		{/if}
	</div>

	<div class="section">
		<button class:active={openSection === 'links'} onclick={() => toggle('links')}>
			Links
			<LinksSidebarTag />
		</button>

		{#if openSection === 'links'}
			<div class="popover" use:clickOutside={{ callback: () => closeIfOpen('links') }}>
				<span class="pointer"></span>
				<div class="popover-body">
					<Links />
				</div>
			</div>
		{/if}
	</div>

	<div class="section">
		<button class:active={openSection === 'ai'} onclick={() => toggle('ai')}> AI Agent </button>

		{#if openSection === 'ai'}
			<div class="popover" use:clickOutside={{ callback: () => closeIfOpen('ai') }}>
				<span class="pointer"></span>
				<div class="popover-body flush">
					<Ai />
				</div>
			</div>
		{/if}
	</div>
</div>

<style>
	.post-sections {
		width: 760px;
		display: flex;
		align-items: center;
		justify-content: space-around;
	}

	.section {
		position: relative;
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

	.popover {
		position: absolute;
		top: calc(100% + 10px);
		left: 50%;
		transform: translateX(-50%);
		z-index: 1000;
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

	.popover-body {
		width: 450px;
		height: 600px;
		overflow-y: auto;
		padding: 15px 20px;
		font-size: 14px;
		background-color: var(--box-background);
		border-radius: var(--box-radius);
		box-shadow: var(--box-shadow);
	}

	.popover-body.flush {
		padding: 0;
		overflow: hidden;
		display: flex;
		flex-direction: column;
	}
</style>
