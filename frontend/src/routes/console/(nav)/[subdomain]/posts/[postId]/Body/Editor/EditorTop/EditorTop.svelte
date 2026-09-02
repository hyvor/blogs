<script lang="ts">
	import { onMount } from 'svelte';
	import PublishedEditingStatus from './PublishedEditingStore/PublishedEditingStatus.svelte';
	import AutoTranslate from './AutoTranslate/AutoTranslate.svelte';

	let el: HTMLDivElement | undefined = $state();
	let parent: HTMLDivElement;

	function positionEl() {
		if (!el || !parent) return;

		const { top, left, right } = parent.getBoundingClientRect();

		if (top <= 0) {
			el.classList.add('fixed');
			el.style.left = left + 'px';
			el.style.right = window.innerWidth - right + 'px';
			// keep a 15px margin from the top
			//el.style.top = Math.min(20, -top) + 'px';
		} else {
			el.classList.remove('fixed');
		}
	}

	onMount(() => {
		parent = el?.parentElement as HTMLDivElement;
		positionEl();
	});
</script>

<svelte:window onscrollcapture={positionEl} />

<div class="editor-top" bind:this={el}>
	<div class="left">
		<PublishedEditingStatus />
	</div>

	<div class="right">
		<AutoTranslate />
	</div>
</div>

<style>
	.editor-top {
		padding: 15px 25px;
		border-bottom: 1px solid var(--border);
		display: flex;
		transition:
			0.3s border-radius,
			0.3s box-shadow;
	}
	.editor-top:global(.fixed) {
		position: fixed;
		z-index: 10;
		top: var(--top-offset, 0);
		background-color: var(--box-background);
		box-shadow: var(--box-shadow);
		border-radius: var(--box-radius);
		border-bottom: none;
	}
	.left {
		flex: 1;
		display: flex;
		align-items: center;
		gap: 8px;
	}
</style>
