<script lang="ts">
	import type { EditorView } from 'prosemirror-view';
	import { onMount } from 'svelte';
	import { generateToc } from './toc';
	import TocChildren from './TocChildren.svelte';
	import TocLevels from './TocLevels.svelte';
	import { IconMessage } from '@hyvor/design/components';

	interface Props {
		view: EditorView;
		levels?: number[];
		getPos: () => number | undefined;
	}

	let { view, levels = [1, 2, 3, 4], getPos }: Props = $props();

	let doc = $state(view.state.doc);

	let toc = $derived(generateToc(doc, levels));

	function handleTransaction(e: any) {
		doc = e.detail.doc;
	}

	onMount(() => {
		// TODO: change this without prosemirror:transaction
		// document.addEventListener('prosemirror:transaction', handleTransaction);
		return () => {
			// document.removeEventListener('prosemirror:transaction', handleTransaction);
		};
	});

	function handleLevelsChange(e: CustomEvent<number[]>) {
		const pos = getPos();
		if (pos === undefined) return;

		view.dispatch(
			view.state.tr.setMeta('addToHistory', false).setNodeMarkup(pos, null, { levels: e.detail })
		);
	}
</script>

<div class="wrap">
	<div class="title" role="heading" aria-level={2}>Table of Contents</div>
	<div class="toc-inner">
		{#if toc.length}
			<TocChildren children={toc} top />
		{:else}
			<IconMessage padding={40} empty iconSize={50} message="No headings found" />
		{/if}
	</div>
	<TocLevels bind:levels on:change={handleLevelsChange} />
</div>

<style>
	.wrap {
		border: 1px solid #ccc;
		border-radius: 5px;
		margin-top: 25px;
		white-space: initial;
	}
	.title {
		padding: 20px 25px;
		font-size: 20px;
		font-weight: 600;
		border-bottom: 1px solid var(--border);
	}
	.toc-inner {
		padding: 20px 25px;
	}
</style>
