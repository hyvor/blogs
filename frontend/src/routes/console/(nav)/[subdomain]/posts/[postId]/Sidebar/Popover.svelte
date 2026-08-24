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

	let triggerEl: HTMLButtonElement | undefined = $state();
	let popoverEl: HTMLDivElement | undefined = $state();
	let position = $state({ top: 0, left: 0 });

	function updatePosition() {
		if (!triggerEl) return;
		const rect = triggerEl.getBoundingClientRect();
		const popoverWidth = popoverEl?.offsetWidth ?? 0;
		position = {
			top: rect.bottom + 10,
			left: rect.left + rect.width / 2 - popoverWidth / 2
		};
	}

	function toggle() {
		show = !show;
	}

	function close() {
		show = false;
	}

	$effect(() => {
		if (!show) return;

		updatePosition();

		window.addEventListener('resize', updatePosition);
		window.addEventListener('scroll', updatePosition, true);

		return () => {
			window.removeEventListener('resize', updatePosition);
			window.removeEventListener('scroll', updatePosition, true);
		};
	});
</script>

<div class="popover-wrap">
	<button bind:this={triggerEl} class="trigger" class:active={show} onclick={toggle}>
		{@render trigger?.()}
	</button>

	{#if show}
		<div
			class="backdrop"
			role="presentation"
			onclick={close}
			transition:fade={{ duration: 60 }}
		></div>

		<div
			bind:this={popoverEl}
			class="popover"
			style:top="{position.top}px"
			style:left="{position.left}px"
			transition:scale={{ duration: 60, start: 0.96, opacity: 0.9 }}
		>
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
		position: fixed;
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
