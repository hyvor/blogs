<script lang="ts">
	import { createEventDispatcher, onMount } from "svelte";
	import { postEditingStatusStore } from "../../../../../postStore";
	import { NodeSelection, type Selection } from "prosemirror-state";
	import { ActionList, ActionListItem, Button, Tooltip, Text } from "@hyvor/design/components";
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

    function setSelection(event: MouseEvent) {
        const selection = editorView.state.selection;
        // Handle selection of the first node of the document
        if (selection.$anchor.pos - selection.$anchor.parentOffset <= 1) {
            editorView.dispatch(editorView.state.tr.setSelection(NodeSelection.create(editorView.state.doc, 1)));
            return;
        }
        editorView.dispatch(editorView.state.tr.setSelection(NodeSelection.create(editorView.state.doc, selection.$anchor.pos)));
    }

    function onClick(event: MouseEvent) {
        showMenu = true;
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
            <ActionList>
                <ActionListItem on:click={() => dispatch('duplicate')}>
                    <IconCopy slot="start" />
                    Duplicate
                    <div slot="description">Duplicate the current node.</div>
                </ActionListItem>
                <ActionListItem type="danger" on:click={() => dispatch('delete')}>
                    <IconTrash slot="start" />
                    Delete
                    <div slot="description">Delete the current node.</div>
                </ActionListItem>
            </ActionList>
        </div>
    {/if}
    <Tooltip text="Click to open menu">
        <button class="dots-button" on:mouseenter={setSelection} on:click={onClick}>
            ::
        </button>
    </Tooltip>
    
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
        width: 220px;
        flex-direction: column;
        position: absolute;
        border: 1px solid var(--gray-light);
        background-color: white;
        border-radius: 5px;
        left: -220px;
        border-radius: 20px;
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
        border-radius: 5px;
    }

    .dots-button:hover {
        background-color: var(--gray-light);
        transform: scale(1.1);
    }

</style>