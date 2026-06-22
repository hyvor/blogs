<script lang="ts">
	import Nav from './Nav/Nav.svelte';
	import { page } from '$app/state';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	const isPostPage = $derived(page.url.pathname.match(/\/console\/[^\/]+\/posts\/[^\/]+/) != null);
</script>

<main id="blog-main">
	{#if !isPostPage}
		<div id="nav">
			<Nav />
		</div>
	{/if}
	<div id="content" class:post-page={isPostPage}>
		{@render children?.()}
	</div>
</main>

<style>
	main#blog-main {
		display: flex;
		width: 100%;
		height: calc(100vh - var(--top-offset, 0));
		flex: 1;
		min-height: 0;
	}
	#nav {
		width: 280px;
		padding: 15px;
		padding-right: 0;
	}
	#content {
		padding: 15px;
		flex: 1;
		height: 100%;
		min-width: 0;
		overflow: auto;
	}

	#content.post-page {
		padding: 0;
	}

	@media (max-width: 992px) {
		main#blog-main {
			flex-direction: column;
			height: initial;
		}
		#nav {
			margin-top: 15px;
			padding: 0;
			width: 100%;
		}
		#content {
			padding: 15px 0;
		}
	}
</style>
