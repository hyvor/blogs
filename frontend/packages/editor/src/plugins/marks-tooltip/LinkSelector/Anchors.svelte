<script lang="ts">
  import { createBubbler } from "svelte/legacy";

  const bubble = createBubbler();
  import { createEventDispatcher } from "svelte";
  import { getHeadingsFromContent, type Heading } from "../../../helpers";
  import { IconMessage, Tag } from "@hyvor/design/components";
  import { editorContent } from "../../../store";

  let headings: Heading[] = $state([]);

  const dispatch = createEventDispatcher();

  function handleAdd(id: string | null) {
    if (!id) return;
    dispatch("add", "#" + id);
  }

  editorContent.subscribe((value) => {
    headings = getHeadingsFromContent(value);
  });
</script>

{#if !headings.length}
  <IconMessage
    padding={50}
    empty
    message="No headings in your post"
    iconSize={50}
  />
{:else}
  <div class="headings">
    {#each headings as heading}
      <div
        class="heading"
        onclick={() => handleAdd(heading.id)}
        role="button"
        onkeyup={bubble("keyup")}
        tabindex="0"
        class:has-id={!!heading.id}
      >
        <span class="type">
          <Tag size="small">
            H{heading.level}
          </Tag>
        </span>

        <span class="text">
          {heading.text}
        </span>

        <span class="link">
          {#if heading.id}
            #{heading.id}
          {:else}
            <span class="missing"> Missing ID </span>
          {/if}
        </span>
      </div>
    {/each}
  </div>
{/if}

<style lang="scss">
  .headings {
    max-height: 400px;
    overflow: auto;

    .type {
      margin-right: 8px;
    }

    .heading {
      display: flex;
      padding: 6px 15px;
      align-items: center;
      border-radius: var(--box-radius);
      cursor: not-allowed;

      &.has-id {
        cursor: pointer;

        &:hover {
          background: var(--hover);
        }
      }
    }

    .text {
      flex: 1;
      font-weight: 600;
    }

    margin-bottom: 25px;
  }

  .missing {
    color: var(--text-light);
    font-size: 12px;
  }
</style>
