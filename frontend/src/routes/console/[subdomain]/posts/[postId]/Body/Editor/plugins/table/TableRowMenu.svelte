<script lang="ts">
	import { IconArrowDown, IconArrowUp, IconBackspace, IconCardHeading, IconThreeDotsVertical, IconTrash } from "@hyvor/icons";
	import { Node } from "prosemirror-model";
	import { onMount } from "svelte";
	import { postEditingStatusStore } from "../../../../../postStore";
	import { TextSelection, type Selection } from "prosemirror-state";
	import { ActionList, ActionListItem, Dropdown } from "@hyvor/design/components";
	import { addRowAfter, addRowBefore, deleteRow, deleteTable, toggleHeaderRow } from "prosemirror-tables";
	import schema from "../../../../../../../lib/prosemirror/schema";

    let show = false;
    let wrapEl: HTMLSpanElement;
    let showDropdown = false;

    $: editorView = $postEditingStatusStore.editorView!;

    function isSelectionInTable(selection: Selection) {
        const pos = selection.$anchor;
        let index = pos.depth;
        while (index > 0) {
            if (pos.node(index).type.name === "table") {
                return true;
            }
            index--;
        }
        return false;
    }

    function position() {
        const view = $postEditingStatusStore.editorView;
        if (!view) return;

        const selection = view.state.selection;
        const selectionInTable = isSelectionInTable(selection);

        if (selectionInTable) {
            show = true;

            let domNode = view.domAtPos(selection.$anchor.pos).node;
            if (domNode.nodeType === 3) domNode = domNode.parentNode!;
            if (!(domNode instanceof HTMLElement)) return;

            const tr = domNode.closest("tr");
            if (!tr) return;

            const { top, left, height } = tr.getBoundingClientRect();

            wrapEl.style.top = `${top}px`;
            wrapEl.style.left = `${left}px`;
            wrapEl.style.height = height + "px";

        } else {
            show = false;
        }
    }

    function close() {
        showDropdown = false;
        editorView.focus();
    }

    function handleHeader() {
        toggleHeaderRow(editorView.state, editorView.dispatch);
        close();
    }

    function handleInsertAbove() {
        addRowBefore(editorView.state, editorView.dispatch);
        close();
    }

    function handleInsertBelow() {
        addRowAfter(editorView.state, editorView.dispatch);
        close();
    }

    function handleDelete() {

        const domNode = editorView.domAtPos(editorView.state.selection.$anchor.pos).node;
        if (domNode instanceof HTMLElement) {
            const table = domNode.closest("table");
            if (table) {
                const trsCount = table.querySelectorAll("tr").length;
                if (trsCount === 1) {
                    deleteTable(editorView.state, editorView.dispatch);
                    close();
                    return;
                }
            }
        }

        deleteRow(editorView.state, editorView.dispatch);
        close();
    }

    function handleClearContent() {
        const pos = editorView.state.selection.$anchor;
        let index = pos.depth;
        let node : Node;
        while (index > 0) {
            node = pos.node(index);
            if (node.type.name === "table_row") {
                const rowPos = pos.before(index);
                const tr = editorView.state.tr;
                tr
                    .replaceWith(
                        rowPos, rowPos + node.nodeSize,
                        schema.nodes.table_row!.createAndFill()!
                    )
                    .setSelection(TextSelection.create(tr.doc, rowPos));
                editorView.dispatch(tr);
                return close();
            }
            index--;
        }
    }

    onMount(position);
</script>

<svelte:window on:scroll|capture={position} />

<span 
    bind:this={wrapEl}
    class:show={show}
    class="wrap"
>
    <Dropdown bind:show={showDropdown}>
        <button slot="trigger">
            <IconThreeDotsVertical size={14} />
        </button>

        <ActionList slot="content">
            <ActionListItem on:click={handleHeader}>
                <IconCardHeading slot="start" />
                Header row
            </ActionListItem>
            <ActionListItem on:click={handleInsertAbove}>
                <IconArrowUp slot="start" />
                Insert above
            </ActionListItem>
            <ActionListItem on:click={handleInsertBelow}>
                <IconArrowDown slot="start" />
                Insert below
            </ActionListItem>
            <ActionListItem on:click={handleDelete}>
                <IconTrash slot="start" />
                Delete row
            </ActionListItem>
            <ActionListItem on:click={handleClearContent}>
                <IconBackspace slot="start" />
                Clear content
            </ActionListItem>
        </ActionList>

    </Dropdown>
</span>

<style>
    .wrap {
        position: fixed;
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
        transform: translateX(-50%);
    }
    .wrap.show {
        display: inline-flex;
    }
    button {
        background-color: var(--input);
        width: 16px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        transition: .2s box-shadow;
    }
    button:hover {
        box-shadow: 0 0 0 2px var(--gray-light);
    }
</style>