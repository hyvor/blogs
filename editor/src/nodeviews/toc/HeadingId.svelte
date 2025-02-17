<script lang="ts">
  import { createBubbler } from "svelte/legacy";

  const bubble = createBubbler();
  import { Tag, TextInput, Tooltip } from "@hyvor/design/components";
  import type { TocEntry } from "./toc";
  import { tick } from "svelte";
  import IconExclamationCircle from "@hyvor/icons/IconExclamationCircle";
  import { editorStore } from "../../store";

  interface Props {
    heading: TocEntry;
  }

  let { heading }: Props = $props();

  let input: HTMLInputElement | undefined = $state();

  function handleClick(e: any) {
    e.stopPropagation();
  }

  function handleKeyup(e: any) {
    if (e.key === "Enter") {
      editing = false;
      updateId(e.target.value);
    } else if (e.key === "Escape") {
      editing = false;
    }
  }

  function updateId(newId: string) {
    const editorView = $editorStore.view;

    if (!editorView) return;

    editorView.dispatch(
      editorView.state.tr.setNodeMarkup(heading.pos, undefined, {
        level: heading.level,
        id: newId,
      })
    );
  }

  async function startEditing() {
    editing = true;
    await tick();
    input && input.focus();
  }

  let editing = $state(false);
</script>

<div
  class="heading-id"
  onclick={handleClick}
  onkeyup={bubble("keyup")}
  role="button"
  tabindex="0"
>
  {#if editing}
    <div class="text-input">
      <TextInput
        size="x-small"
        value={heading.id || ""}
        style="width:100px"
        autofocus
        on:blur={() => (editing = false)}
        on:keyup={handleKeyup}
        bind:input
      >
        {#snippet start()}
          <span> ID # </span>
        {/snippet}
      </TextInput>
    </div>
  {:else}
    <Tooltip text="Click to edit ID">
      <button onclick={startEditing}>
        {#if heading.id}
          <span class="id">#{heading.id}</span>
        {:else}
          <Tag color="red" size="x-small">
            {#snippet start()}
              <IconExclamationCircle size={10} />
            {/snippet}
            No ID
          </Tag>
        {/if}
      </button>
    </Tooltip>
  {/if}
</div>

<style>
  .text-input :global(.start) {
    margin-right: 1px !important;
  }

  .id {
    font-size: 14px;
    color: #666;
  }
</style>
