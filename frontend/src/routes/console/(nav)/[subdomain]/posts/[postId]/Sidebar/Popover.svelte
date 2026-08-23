<script lang="ts">
	import type { Snippet } from 'svelte';
	import { fade, scale } from 'svelte/transition';

	interface Props {
		show?: boolean;
		flush?: boolean;
		trigger?: Snippet;
		children?: Snippet;
	}

	let { show = $bindable(false), flush = false, trigger, children }: Props = $props();

	function toggle() {
		show = !show;
	}

	function close() {
		show = false;
	}
</script>

<div class="popover-wrap">
	<button class="trigger" class:active={show} onclick={toggle}>
		{@render trigger?.()}
	</button>

	{#if show}
		<div
			class="backdrop"
			role="presentation"
			onclick={close}
			transition:fade={{ duration: 120 }}
		></div>

		<div class="popover" transition:scale={{ duration: 120, start: 0.96, opacity: 0.9 }}>
			<span class="pointer"></span>
			<div class="popover-body" class:flush>
				{@render children?.()}
			</div>
		</div>
	{/if}
</div>

<style>
	.popover-wrap {
		position: relative;
		height: 100%;
	}

	.trigger {
		position: relative;
		z-index: 501;
		height: 100%;
		font-size: 14px;
		display: inline-flex;
		gap: 5px;
		align-items: center;
		padding: 0 8px;
		border-radius: 6px;
		transition: color 0.2s ease;
	}

	.trigger:hover,
	.trigger.active {
		color: var(--accent);
	}

	.backdrop {
		position: fixed;
		inset: 0;
		background-color: rgba(0, 0, 0, 0.15);
		z-index: 500;
	}

	.popover {
		position: absolute;
		top: calc(100% + 10px);
		left: 50%;
		transform: translateX(-50%);
		z-index: 501;
	}

	.pointer {
		position: absolute;
		top: -6px;
		left: 50%;
		width: 12px;
		height: 12px;
		transform: translateX(-50%) rotate(45deg);
		background-color: var(--box-background);
		box-shadow: -1px -1px 1px rgba(0, 0, 0, 0.04);
		border-radius: 2px;
	}

	.popover-body {
		width: 500px;
		height: 700px;
		overflow-y: auto;
		padding: 15px 20px;
		font-size: 14px;
		background-color: var(--box-background);
		border-radius: var(--box-radius);
		box-shadow: var(--box-shadow);
	}

	.popover-body.flush {
		padding: 0;
		overflow: hidden;
		display: flex;
		flex-direction: column;
	}
</style>
