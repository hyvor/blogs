<script lang="ts">
	import { onMount, tick } from 'svelte';
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
	import IconChatRight from '@hyvor/icons/IconChatRight';
	import { deleteNode, nodeMenuPos } from './node-menu';

	interface Props {
		view: EditorView;
	}

	let { view }: Props = $props();

	let show = $state(false);
	let wrapEl: HTMLSpanElement | undefined = $state();

	function isSelectionDragable(selection: Selection) {
		const pos = selection.$anchor;
		const index = pos.depth;
		const node = pos.node(index);
		if (node.type.spec.draggable) {
			return true;
		}
		return false;
	}

	function position() {
		if (!wrapEl) return;
		if ($nodeMenuPos === null) return;

		const selection = view.state.selection;

		//if (selectionDraggable) {
		// show = true;

		let domNode = view.domAtPos($nodeMenuPos).node;
		if (domNode.nodeType === 3) domNode = domNode.parentNode!;
		if (!(domNode instanceof HTMLElement)) return;

		let { left, top, height } = domNode.getBoundingClientRect();

		left -= 22;
		top += height / 2 - 10;

		wrapEl.style.top = `${top}px`;
		wrapEl.style.left = `${left}px`;
		// } else {
		// 	wrapEl.style.display = 'none';
		// }
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

	nodeMenuPos.subscribe(async () => {
		await tick();
		position();
	});

	function setSelection(event: MouseEvent) {
		// Handle selection of the first node of the document
		// if (selection.$anchor.pos - selection.$anchor.parentOffset <= 1) {
		// 	editorView.dispatch(
		// 		editorView.state.tr.setSelection(NodeSelection.create(editorView.state.doc, 1))
		// 	);
		// 	return;
		// }

		if (!$nodeMenuPos) return;

		const tr = view.state.tr;
		view.dispatch(tr.setSelection(NodeSelection.create(tr.doc, $nodeMenuPos - 1)));
	}

	function onClick(event: MouseEvent) {
		// setSelection(event);
	}

	let dragging = $state(false);
	let dragEl: HTMLDivElement | undefined = $state();

	function positionDrag(event: MouseEvent) {
		if (!dragEl) return;
		dragEl.style.left = `${event.clientX + 15}px`;
		dragEl.style.top = `${event.clientY - 15}px`;
	}

	function onMouseDown(event: MouseEvent) {
		if ($nodeMenuPos === null) return;
		if (!dragEl) return;

		let domNode = view.domAtPos($nodeMenuPos).node;

		const copy = domNode.cloneNode(true) as HTMLElement;
		dragEl.innerHTML = '';
		dragEl.appendChild(copy);
		positionDrag(event);

		dragging = true;
	}

	function onMouseMove(event: MouseEvent) {
		// return;
		if (!dragEl) return;
		if (!dragging) return;
		positionDrag(event);
	}

	function onMouseUp(event: MouseEvent) {
		if (!dragEl) return;
		if (dragging) {
			dragging = false;
			dragEl.innerHTML = '';
		}
	}

	function onDelete() {
		if (!$nodeMenuPos) return;
		deleteNode(view, $nodeMenuPos);
		show = false;
	}
</script>

<svelte:window onscrollcapture={position} />
<svelte:body onmousemove={onMouseMove} onmouseup={onMouseUp} />

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

<div class="drag-wrap" bind:this={dragEl}></div>

<div class="wrap" bind:this={wrapEl} class:show={$nodeMenuPos !== null}>
	<Dropdown bind:show width={250}>
		{#snippet content()}
			<ActionList>
				<ActionListItem>
					{#snippet start()}
						<IconChatRight size={14} />
					{/snippet}
					Comment
				</ActionListItem>
				<ActionListItem>
					{#snippet start()}
						<IconCopy size={14} />
					{/snippet}
					Duplicate
				</ActionListItem>
				<ActionListItem type="danger" on:click={onDelete}>
					{#snippet start()}
						<IconTrash size={14} />
					{/snippet}
					Delete
				</ActionListItem>
			</ActionList>
		{/snippet}

		{#snippet trigger()}
			<Tooltip text="Click to open menu, drag to move">
				<!-- svelte-ignore a11y_no_static_element_interactions -->
				<span onmousedown={onMouseDown} style="color: var(--text-light)">
					<IconButton size={20} color="input" variant="invisible">
						<IconGripVertical size={14} />
					</IconButton>
				</span>
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

	.drag-wrap {
		position: fixed;
		z-index: 100;
		display: block;
		pointer-events: none;
		opacity: 0.5;
		font-size: 18px;
	}
</style>
