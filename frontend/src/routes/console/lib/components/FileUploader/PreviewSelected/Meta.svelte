<script lang="ts">
	interface Props {
		name: string;
		children?: import('svelte').Snippet;
	}

	let { name, children }: Props = $props();
</script>

<div class="meta">
	<div class="name">{name}</div>
	<div class="value">{@render children?.()}</div>
</div>

<style>
	.meta {
		display: flex;
		flex-direction: column;
		padding: 0 15px;
	}

	.name {
		font-size: 13px;
		color: var(--text-light);
		margin-bottom: 10px;
	}
	.value {
		font-weight: 600;
	}
</style>
