<script lang="ts">
  import IconArrowDown from "@hyvor/icons/IconArrowDown";
  import IconArrowUp from "@hyvor/icons/IconArrowUp";
  import IconBackspace from "@hyvor/icons/IconBackspace";
  import IconCardHeading from "@hyvor/icons/IconCardHeading";
  import IconThreeDotsVertical from "@hyvor/icons/IconThreeDotsVertical";
  import IconTrash from "@hyvor/icons/IconTrash";
  import { Node } from "prosemirror-model";
  import { tick } from "svelte";
  import { TextSelection, type Selection } from "prosemirror-state";
  import {
    ActionList,
    ActionListItem,
    Dropdown,
  } from "@hyvor/design/components";
  import {
    addRowAfter,
    addRowBefore,
    deleteRow,
    deleteTable,
    toggleHeaderRow,
  } from "prosemirror-tables";
  import schema from "../../schema";
  import { editorStore } from "../../store";

  let { updateId }: { updateId: number } = $props();

  let show = $state(false);
  let wrapEl: HTMLSpanElement | undefined = $state(undefined);
  let showDropdown = $state(false);

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

  async function position() {
    const view = $editorStore.view;
    if (!view) return;

    const selection = view.state.selection;
    const selectionInTable = isSelectionInTable(selection);

    if (selectionInTable) {
      show = true;

      await tick();

      let domNode = view.domAtPos(selection.$anchor.pos).node;
      if (domNode.nodeType === 3) domNode = domNode.parentNode!;
      if (!(domNode instanceof HTMLElement)) return;

      const tr = domNode.closest("tr");
      if (!tr) return;

      const { top, left, height } = tr.getBoundingClientRect();

      if (wrapEl) {
        const wrapElRect = wrapEl.getBoundingClientRect();
        wrapEl.style.top = `${top}px`;
        wrapEl.style.left = left - wrapElRect.width / 2 + "px";
        wrapEl.style.height = height + "px";
      }
    } else {
      show = false;
    }
  }

  function close() {
    showDropdown = false;
    $editorStore.view.focus();
  }

  function handleHeader() {
    toggleHeaderRow($editorStore.view.state, $editorStore.view.dispatch);
    close();
  }

  function handleInsertAbove() {
    addRowBefore($editorStore.view.state, $editorStore.view.dispatch);
    close();
  }

  function handleInsertBelow() {
    addRowAfter($editorStore.view.state, $editorStore.view.dispatch);
    close();
  }

  function handleDelete() {
    const domNode = $editorStore.view.domAtPos(
      $editorStore.view.state.selection.$anchor.pos
    ).node;
    if (domNode instanceof HTMLElement) {
      const table = domNode.closest("table");
      if (table) {
        const trsCount = table.querySelectorAll("tr").length;
        if (trsCount === 1) {
          deleteTable($editorStore.view.state, $editorStore.view.dispatch);
          close();
          return;
        }
      }
    }

    deleteRow($editorStore.view.state, $editorStore.view.dispatch);
    close();
  }

  function handleClearContent() {
    const pos = $editorStore.view.state.selection.$anchor;
    let index = pos.depth;
    let node: Node;
    while (index > 0) {
      node = pos.node(index);
      if (node.type.name === "table_row") {
        const rowPos = pos.before(index);
        const tr = $editorStore.view.state.tr;
        tr.replaceWith(
          rowPos,
          rowPos + node.nodeSize,
          schema.nodes.table_row!.createAndFill()!
        ).setSelection(TextSelection.create(tr.doc, rowPos));
        $editorStore.view.dispatch(tr);
        return close();
      }
      index--;
    }
  }

  $effect(() => {
    updateId;
    position();
  });
</script>

<svelte:window onscrollcapture={position} />

<span bind:this={wrapEl} class:show class="wrap">
  <Dropdown bind:show={showDropdown} position="right">
    {#snippet trigger()}
      <button>
        <IconThreeDotsVertical size={14} />
      </button>
    {/snippet}

    {#snippet content()}
      <ActionList>
        <ActionListItem on:click={handleHeader}>
          {#snippet start()}
            <IconCardHeading />
          {/snippet}
          Header row
        </ActionListItem>
        <ActionListItem on:click={handleInsertAbove}>
          {#snippet start()}
            <IconArrowUp />
          {/snippet}
          Insert above
        </ActionListItem>
        <ActionListItem on:click={handleInsertBelow}>
          {#snippet start()}
            <IconArrowDown />
          {/snippet}
          Insert below
        </ActionListItem>
        <ActionListItem on:click={handleDelete}>
          {#snippet start()}
            <IconTrash />
          {/snippet}
          Delete row
        </ActionListItem>
        <ActionListItem on:click={handleClearContent}>
          {#snippet start()}
            <IconBackspace />
          {/snippet}
          Clear content
        </ActionListItem>
      </ActionList>
    {/snippet}
  </Dropdown>
</span>

<style>
  .wrap {
    position: fixed;
    z-index: 100;
    display: none;
    align-items: center;
    justify-content: center;
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
    transition: 0.2s box-shadow;
  }

  button:hover {
    box-shadow: 0 0 0 2px var(--gray-light);
  }
</style>
