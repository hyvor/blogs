<script lang="ts">
	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();
</script>

<div class="top">
	{@render children?.()}
</div>

<style>
	.top {
		display: flex;
		justify-content: flex-end;
		padding: 15px;
		margin-bottom: 15px;
		border-bottom: 1px solid var(--border);
	}
</style>
