<script lang="ts">
	interface Props {
		pending?: boolean;
		size?: number;
		children: import('svelte').Snippet;
	}

	let { pending = false, size = 100, children }: Props = $props();

	const scale = $derived(size / 100);
</script>

<div class="seal" class:pending style="--scale: {scale};">
	<div class="seal-inner">
		{@render children()}
	</div>
</div>

<style>
	.seal {
		position: relative;
		display: flex;
		align-items: center;
		justify-content: center;
		width: calc(var(--scale) * 100px);
		height: calc(var(--scale) * 100px);
		border-radius: calc(var(--scale) * 100px);
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

	.seal-inner {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		gap: 5px;
		width: 100px;
		height: 100px;
		transform: scale(var(--scale));
	}

	@media (max-width: 768px) {
		.seal {
			width: calc(var(--scale) * 76px);
			height: calc(var(--scale) * 76px);
			border-radius: calc(var(--scale) * 76px);
		}

		.seal-inner {
			transform: scale(calc(var(--scale) * 0.76));
		}
	}
</style>
