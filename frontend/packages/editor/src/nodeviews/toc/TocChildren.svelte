<script lang="ts">
  import TocChildren from "./TocChildren.svelte";
  import { createBubbler } from "svelte/legacy";

  const bubble = createBubbler();
  import { Tag } from "@hyvor/design/components";
  import type { TocEntry } from "./toc";
  import { get } from "svelte/store";
  import { TextSelection } from "prosemirror-state";
  import { positionSelectionInMiddleOfScreen } from "../../helpers";
  import HeadingId from "./HeadingId.svelte";
  import { editorStore } from "../../store";

  interface Props {
    children: TocEntry[];
    top?: boolean;
  }

  let { children, top = false }: Props = $props();

  function handleHeadingClick(entry: TocEntry) {
    const editorView = $editorStore.view;
    if (!editorView) return;

    const doc = editorView.state.doc;
    const pos = entry.pos;

    const resolvedPos = doc.resolve(pos);
    const selection = TextSelection.create(
      doc,
      pos + resolvedPos.nodeAfter!.nodeSize - 1
    );

    editorView.dispatch(
      editorView.state.tr.setSelection(selection).scrollIntoView()
    );
    editorView.focus();

    positionSelectionInMiddleOfScreen(editorView);
  }
</script>

<div class="toc-ul" class:top>
  {#each children as child}
    <div class="toc-li">
      <div
        class="heading"
        onclick={(e) => handleHeadingClick(child)}
        onkeyup={bubble("keyup")}
        role="button"
        tabindex="0"
      >
        <Tag size="x-small">
          <strong>H{child.level}</strong>
        </Tag>

        <div class="title">{child.title}</div>

        <div class="dots"></div>

        <HeadingId heading={child} />
      </div>

      {#if child.children.length > 0}
        <TocChildren children={child.children} />
      {/if}
    </div>
  {/each}
</div>

<style lang="scss">
  .toc-ul:not(.top) {
    padding-left: 25px;
  }

  .toc-li {
    display: flex;
    flex-direction: column;
  }

  .heading {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 10px;
    border-radius: 20px;
    cursor: pointer;

    &:hover {
      background-color: var(--hover);
    }
  }

  .title {
    font-size: 16px;
  }

  .dots {
    flex: 1;
    border-top: 1px dashed #ccc;
  }
</style>
