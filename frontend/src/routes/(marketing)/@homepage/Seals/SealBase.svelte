<script lang="ts">
	interface Props {
		pending?: boolean;
		children: import('svelte').Snippet;
	}

	let { pending = false, children }: Props = $props();
</script>

<div class="seal" class:pending>
	{@render children()}
</div>

<style>
	.seal {
		position: relative;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		gap: 5px;
		width: 100px;
		height: 100px;
		border-radius: 100px;
		background: radial-gradient(
			circle at 50% 42%,
			rgba(255, 255, 255, 0.09),
			rgba(255, 255, 255, 0.015) 72%
		);
	}

	.seal.pending {
		background: none;
		border: 1.5px dashed rgba(255, 255, 255, 0.16);
	}

	@media (max-width: 768px) {
		.seal {
			width: 76px;
			height: 76px;
		}
	}
</style>
