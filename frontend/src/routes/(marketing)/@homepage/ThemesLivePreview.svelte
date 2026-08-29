<script lang="ts">
	import { Loader } from '@hyvor/design/components';
	import ThemesPreview from '../@components/ThemesPreview.svelte';

	let isLoading = $state(true);
</script>

<div class="themes-live">
	{#if isLoading}
		<Loader full />
	{/if}

	<ThemesPreview
		on:load={() => (isLoading = false)}
		lockScroll={true}
		hideDeviceToggle={true}
		hideOpenInNewTab={true}
		hideOpenSource={true}
	/>

	<div class="fade-overlay" aria-hidden="true"></div>
</div>

<style>
	.themes-live {
		--left-pull: 380px;
		--right-bleed: calc(max(0px, (100vw - 1000px) / 2) + 72px);
		position: relative;
		margin-top: 64px;
		margin-left: calc(-1 * var(--left-pull));
		height: 640px;
		width: calc(100% + var(--right-bleed) + var(--left-pull));
		border-radius: 20px 0 0 20px;
		border: 1px solid var(--border);
		border-right: none;
		overflow: hidden;
		background: var(--background);
		box-shadow: 0 16px 48px color-mix(in srgb, var(--text) 8%, transparent);
	}

	.fade-overlay {
		position: absolute;
		inset: 0;
		z-index: 1;
		pointer-events: none;
		background: linear-gradient(
			to right,
			var(--background) 0%,
			var(--background) 15%,
			color-mix(in srgb, var(--background) 75%, transparent) 32%,
			color-mix(in srgb, var(--background) 30%, transparent) 48%,
			transparent 62%
		);
	}

	@media (max-width: 900px) {
		.themes-live {
			display: none;
		}
	}
</style>
