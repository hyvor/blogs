<script lang="ts">
	import {
		Docs,
		DocsNav as Nav,
		DocsNavCategory as NavCategory,
		DocsNavItem as NavItem,
		DocsContent as Content
	} from '@hyvor/design/marketing';
	import { categories } from '../hosting';

	let { data } = $props();
</script>

<svelte:head>
	<title>{data.name} | Hosting | Hyvor Blogs</title>
	<link rel="canonical" href="https://blogs.hyvor.com/hosting{data.slug ? '/' + data.slug : ''}" />
</svelte:head>

<div class="docs-wrap">
	<Docs>
		{#snippet nav()}
			<Nav>
				{#each categories as category}
					<NavCategory name={category.name}>
						{#each category.pages as page}
							<NavItem href={page.slug === '' ? '/hosting' : `/hosting/${page.slug}`}>
								{page.name}
							</NavItem>
						{/each}
					</NavCategory>
				{/each}
			</Nav>
		{/snippet}
		{#snippet content()}
			<Content>
				{@const Component = data.component}
				<Component />
			</Content>
		{/snippet}
	</Docs>
</div>

<style>
	.docs-wrap :global(.nav-items a.active) {
		background-color: var(--accent-light-mid) !important;
	}
</style>
