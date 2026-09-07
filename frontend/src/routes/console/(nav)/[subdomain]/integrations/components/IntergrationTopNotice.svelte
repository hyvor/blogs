<script lang="ts">
	import IconInfoCircle from '@hyvor/icons/IconInfoCircle';
	import type { Snippet } from 'svelte';

	interface Props {
		children: Snippet;
	}

	let { children }: Props = $props();
</script>

<div class="top">
	<IconInfoCircle size={12} />
	<div>
		{@render children()}
	</div>
</div>

<style>
	.top {
		padding: 15px 35px;
		border-bottom: 1px solid var(--border);
		background-color: var(--blue-light);
		color: var(--blue-dark);
		font-size: 14px;
		display: flex;
		gap: 6px;
		align-items: center;
	}
</style>
