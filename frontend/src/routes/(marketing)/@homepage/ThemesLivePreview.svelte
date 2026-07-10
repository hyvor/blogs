<script lang="ts">
	import { Loader } from '@hyvor/design/components';
	import ThemesPreview from '../@components/ThemesPreview.svelte';

	let isLoading = $state(true);
</script>

<div class="themes-live">
	{#if isLoading}
		<Loader full />
	{/if}

	<ThemesPreview on:load={() => (isLoading = false)} lockScroll={true} />
</div>

<style>
	.themes-live {
		margin-top: 64px;
		height: 640px;
		border-radius: 20px;
		border: 1px solid var(--border);
		overflow: hidden;
		background: var(--background);
		box-shadow: 0 16px 48px color-mix(in srgb, var(--text) 8%, transparent);
	}

	@media (max-width: 900px) {
		.themes-live {
			margin-top: 48px;
			height: 700px;
		}
	}
</style>
