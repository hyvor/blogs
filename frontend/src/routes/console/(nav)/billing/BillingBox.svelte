<script lang="ts">
	interface Props {
		title?: null | string;
		style?: undefined | string;
		padding?: number;
		children?: import('svelte').Snippet;
	}

	let {
		title = null,
		style = undefined,
		padding = 30,
		children
	}: Props = $props();
</script>

<div class="wrap hds-box" {style} style:padding="{padding}px">
	{#if title}
		<div class="title">{title}</div>
	{/if}

	{@render children?.()}
</div>

<style>
	.wrap {
		margin: 0 5px;
		margin-bottom: 10px;
	}
	.title {
		font-size: 20px;
		font-weight: 600;
		margin-bottom: 15px;
	}

	@media (max-width: 992px) {
		.wrap {
			margin: 0;
			margin-bottom: 10px;
		}
	}
</style>
