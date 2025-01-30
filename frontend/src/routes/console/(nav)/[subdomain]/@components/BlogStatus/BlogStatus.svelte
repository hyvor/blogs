<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import type { Component } from 'svelte';

	interface Props {
		title: string;
		description: string;
		icon: Component;
		cta?: string | null;
	}

	let { title, description, icon, cta = null }: Props = $props();

	const SvelteComponent = $derived(icon);
</script>

<div class="hds-box wrap">
	<div class="inner">
		<div class="icon">
			<SvelteComponent size={60} />
		</div>

		<div class="title" role="heading" aria-level={2}>
			{title}
		</div>

		<div class="description">
			{description}
		</div>

		{#if cta}
			<div class="cta">
				<Button on:click>
					{cta}
				</Button>
			</div>
		{/if}
	</div>
</div>

<style>
	.wrap {
		flex: 1;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 100px 0;
	}
	.inner {
		width: 400px;
		max-width: 100%;
	}
	.title {
		margin-top: 15px;
		font-weight: 600;
		font-size: 20px;
	}
	.description {
		margin-top: 15px;
		margin-bottom: 15px;
	}
</style>
