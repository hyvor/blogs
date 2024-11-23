<script lang="ts">
	import { createEventDispatcher, onMount } from "svelte";
	import { postEditingStatusStore } from "../../../../../postStore";
	import { NodeSelection, type Selection } from "prosemirror-state";
	import { Button } from "@hyvor/design/components";
	import { IconCopy, IconTrash } from "@hyvor/icons";
    
    let show = false;
    let showMenu = false;
    let wrapEl: HTMLSpanElement;

    $: editorView = $postEditingStatusStore.editorView!;

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

        const selection = view.state.selection;
        const selectionDraggable = isSelectionDragable(selection);

        if (selectionDraggable) {
            show = true;

            let domNode = view.domAtPos(selection.$anchor.pos).node;
            if (domNode.nodeType === 3) domNode = domNode.parentNode!;
            if (!(domNode instanceof HTMLElement)) return;
            
            let { left, top, height } = domNode.getBoundingClientRect();

            left -= 20;
            top += height / 2 - 10;

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
        showMenu = !showMenu;
        dispatch('drag');
    }

</script>

<svelte:window on:scroll|capture={position} />

<span 
    bind:this={wrapEl}
    class:show={show}
    class="wrap"
>
    {#if showMenu}
        <div class="node-menu">
            <button class="node-menu-action" on:click={() => dispatch('duplicate')}>
                <IconCopy />
                Duplicate
            </button>
            <button class="node-menu-action" on:click={() => dispatch('delete')}>
                <IconTrash />
                Delete
            </button>
        </div>
    {/if}
    <button class="dots-button" on:mousedown={onMouseDown} on:click={onClick}>
        ::
    </button>
    
</span>

<style>
    .wrap {
        position: fixed;
        z-index: 100;
        align-items: center;
        justify-content: center;
    }

    .node-menu {
        display: flex;
        flex-direction: column;
        position: absolute;
        border: 1px solid var(--gray-light);
        background-color: white;
        border-radius: 5px;
        padding: 5px;
        left: -120px;
        border-radius: 20px;
    }

    .node-menu-action {
        display: flex;
        align-items: center;
        padding: 5px;
        gap: 10px;
        cursor: pointer;
        border-radius: 20px;
    }

    .node-menu-action:hover {
        background-color: var(--gray-light);
    }

    .dots-button {
        background-color: transparent;
        border: none;
        cursor: pointer;
        font-size: 16px;
        padding-left: 2px;
        padding-right: 2px;
        padding-bottom: 2px;
        color: var(--gray);
    }

    .dots-button:hover {
        background-color: var(--gray-light);
        transform: scale(1.1);
    }

</style>