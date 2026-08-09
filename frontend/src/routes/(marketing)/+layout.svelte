<script lang="ts">
	import Footer from './Footer.svelte';
	import Header from './Header.svelte';
	import { page } from '$app/stores';
	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();
</script>

<Header />

{@render children?.()}

{#if $page.url.pathname !== '/themes'}
	<Footer />
{/if}

<style>
	/* TODO: remove after adding to design */
	@import url(https://fonts.bunny.net/css?family=source-serif-4:400,600);

	:global(:root) {
		--font-serif: 'Source Serif 4', serif;
	}
</style>
