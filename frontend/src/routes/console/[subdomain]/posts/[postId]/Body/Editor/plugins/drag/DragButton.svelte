<script lang="ts">
	import { IconThreeDotsVertical } from "@hyvor/icons";
	import { createEventDispatcher, onMount } from "svelte";
	import { postEditingStatusStore } from "../../../../../postStore";
	import { NodeSelection, TextSelection, type Selection } from "prosemirror-state";
	import { ActionList, ActionListItem, Dropdown } from "@hyvor/design/components";
	import { addColumnAfter, addColumnBefore, deleteColumn, deleteTable, toggleHeaderColumn } from "prosemirror-tables";
	import schema from "../../../../../../../lib/prosemirror/schema";
	import { selectParentNode } from "prosemirror-commands";

    let show = false;
    let wrapEl: HTMLSpanElement;

    $: editorView = $postEditingStatusStore.editorView!;

    const dispatch = createEventDispatcher<{
        drag: void;
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

        const selection = view.state.selection;
        const selectionDraggable = isSelectionDragable(selection);

        if (selectionDraggable) {
            show = true;

            let domNode = view.domAtPos(selection.$anchor.pos).node;
            if (domNode.nodeType === 3) domNode = domNode.parentNode!;
            if (!(domNode instanceof HTMLElement)) return;
            
            let { left, top, height } = domNode.getBoundingClientRect();

            left -= 20;
            //top += height / 2;

            wrapEl.style.top = `${top}px`;
            wrapEl.style.left = `${left}px`;

        } else {
            wrapEl.style.display = "none";
        }

    }

    onMount(position);

    function onMouseDown(event: MouseEvent) {
        editorView.dispatch(editorView.state.tr.setSelection(NodeSelection.create(editorView.state.doc, editorView.state.selection.$anchor.pos)));
    }

    function onClick(event: MouseEvent) {
        dispatch('drag');
    }

</script>

<svelte:window on:scroll|capture={position} />

<span 
    bind:this={wrapEl}
    class:show={show}
    class="wrap"
>
    <button on:mousedown={onMouseDown} on:click={onClick}>
        <IconThreeDotsVertical size={14} />
    </button>
    
</span>

<style>
    .wrap {
        position: fixed;
        z-index: 100;
        align-items: center;
        justify-content: center;
    }

</style>