<script lang="ts">
	import { Docs } from '@hyvor/design/marketing';
	let { data } = $props();
</script>

<svelte:head>
	<title>{data.page.name} - Hosting Hyvor Blogs</title>
	<link rel="canonical" href="https://blogs.hyvor.com/hosting{data.slug ? '/' + data.slug : ''}" />
</svelte:head>

<div class="docs">
	<Docs {...data} />
</div>

<style>
	.docs {
		margin-bottom: 80px;
	}

	.docs :global(.nav-item.active) {
		background-color: var(--accent-light-mid) !important;
	}
</style>
