<script lang="ts" generics="T extends 'embed'|'link'">
  import { IconMessage, Loader } from "@hyvor/design/components";
  import { onMount } from "svelte";
  import EmbedHtmlDisplay from "../../plugins/slash/Embed/EmbedHtmlDisplay.svelte";
  import BookmarkDisplay from "../../plugins/slash/Bookmark/BookmarkDisplay.svelte";

  interface Props {
    url: string;
    type?: any;
  }

  let { url, type = "embed" as T }: Props = $props();

  let isLoading = $state(true);
  let unfolded: any = $state();
  let error: null | string = $state(null);

  onMount(() => {
    // TODO: fix this
    return;
    /* getUnfold(url, type)
      .then((res) => {
        unfolded = res;
      })
      .catch(() => {
        error = "Failed to load embed";
      })
      .finally(() => {
        isLoading = false;
      }); */
  });
</script>

<div>
  {#if isLoading}
    <Loader block padding={100} />
  {:else if error}
    <IconMessage error padding={60} message={error} iconSize={70} />
  {:else if type === "embed"}
    <EmbedHtmlDisplay url={unfolded.url} />
  {:else}
    <BookmarkDisplay link={unfolded} />
  {/if}
</div>

<style>
</style>
