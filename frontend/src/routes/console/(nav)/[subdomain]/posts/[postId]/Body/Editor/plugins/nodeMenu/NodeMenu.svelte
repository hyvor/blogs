<script lang="ts">
	import { createEventDispatcher, onMount, tick } from 'svelte';
	import { postEditingStatusStore } from '../../../../../postStore';
	import { NodeSelection, type Selection } from 'prosemirror-state';
	import {
		ActionList,
		ActionListItem,
		Dropdown,
		IconButton,
		Tooltip
	} from '@hyvor/design/components';
	import IconCopy from '@hyvor/icons/IconCopy';
	import IconTrash from '@hyvor/icons/IconTrash';
	import type { EditorView } from 'prosemirror-view';
	import IconGripVertical from '@hyvor/icons/IconGripVertical';
	import { nodeMenuPos, nodeMenuUpdateId } from './node-menu';

	interface Props {
		view: EditorView;
	}

	let { view }: Props = $props();

	let show = $state(false);
	let wrapEl: HTMLSpanElement | undefined = $state();

	let editorView = $derived($postEditingStatusStore.editorView!);

	const dispatch = createEventDispatcher<{
		drag: void;
		delete: void;
		duplicate: void;
	}>();

	function isSelectionDragable(selection: Selection) {
		if (!editorView) return;
		const pos = selection.$anchor;
		const index = pos.depth;
		const node = pos.node(index);
		if (node.type.spec.draggable) {
			return true;
		}
		return false;
	}

	function position() {
		const view = $postEditingStatusStore.editorView;
		if (!view) return;
		if (!wrapEl) return;
		if ($nodeMenuPos === null) return;

		const selection = view.state.selection;
		const selectionDraggable = isSelectionDragable(selection);

		if (selectionDraggable) {
			show = true;

			let domNode = view.domAtPos($nodeMenuPos).node;
			if (domNode.nodeType === 3) domNode = domNode.parentNode!;
			if (!(domNode instanceof HTMLElement)) return;

			let { left, top, height } = domNode.getBoundingClientRect();

			left -= 22;
			top += height / 2 - 10;

			wrapEl.style.top = `${top}px`;
			wrapEl.style.left = `${left}px`;
		} else {
			wrapEl.style.display = 'none';
		}
	}

	onMount(position);

	// update position when updateId is changed
	// $effect(() => {
	// 	if (nodeM) {
	// 		(async () => {
	// 			await tick();
	// 			position();
	// 		})();
	// 	}
	// });

	nodeMenuPos.subscribe(() => {
		position();
	});

	function setSelection(event: MouseEvent) {
		const selection = editorView.state.selection;
		// Handle selection of the first node of the document
		if (selection.$anchor.pos - selection.$anchor.parentOffset <= 1) {
			editorView.dispatch(
				editorView.state.tr.setSelection(NodeSelection.create(editorView.state.doc, 1))
			);
			return;
		}
		editorView.dispatch(
			editorView.state.tr.setSelection(
				NodeSelection.create(editorView.state.doc, selection.$anchor.pos)
			)
		);
	}

	function onClick(event: MouseEvent) {
		show = true;
		dispatch('drag');
	}
</script>

<svelte:window onscrollcapture={position} />

<!-- <span bind:this={wrapEl} class:show class="wrap">
	{#if showMenu}
		<div class="node-menu">
			<ActionList>
				<ActionListItem on:click={() => dispatch('duplicate')}>
					{#snippet start()}
						<IconCopy />
					{/snippet}
					Duplicate
					{#snippet description()}
						<div>Duplicate the current node.</div>
					{/snippet}
				</ActionListItem>
				<ActionListItem type="danger" on:click={() => dispatch('delete')}>
					{#snippet start()}
						<IconTrash />
					{/snippet}
					Delete
					{#snippet description()}
						<div>Delete the current node.</div>
					{/snippet}
				</ActionListItem>
			</ActionList>
		</div>
	{/if}
	<Tooltip text="Click to open menu">
		<button class="dots-button" onmouseenter={setSelection} onclick={onClick}>
			<IconThreeDotsVertical />
		</button>
	</Tooltip>
</span> -->

<div class="wrap" bind:this={wrapEl} class:show={$nodeMenuPos !== null}>
	<Dropdown bind:show width={250}>
		{#snippet content()}
			<ActionList>
				<ActionListItem on:click={() => dispatch('duplicate')}>
					{#snippet start()}
						<IconCopy />
					{/snippet}
					Duplicate
				</ActionListItem>
				<ActionListItem type="danger" on:click={() => dispatch('delete')}>
					{#snippet start()}
						<IconTrash />
					{/snippet}
					Delete
				</ActionListItem>
			</ActionList>
		{/snippet}

		{#snippet trigger()}
			<Tooltip text="Click to open menu, drag to move">
				<IconButton size={20} color="input" variant="invisible">
					<IconGripVertical size={14} />
				</IconButton>
			</Tooltip>
		{/snippet}
	</Dropdown>
</div>

<style>
	.wrap {
		position: fixed;
		z-index: 100;
		display: none;
	}
	.wrap.show {
		display: block;
	}
</style>
