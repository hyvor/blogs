<script lang="ts">
  import { onMount } from "svelte";
  import { addParentResizeEvent } from "./iframeParentResize";

  interface Props {
    url: string;
  }

  let { url }: Props = $props();

  // TODO: Get this from the config
  const unfoldHost = "https://hyvor.com";

  onMount(() => {
    addParentResizeEvent();
  });
</script>

<iframe
  src={unfoldHost + "/api/public/unfold/iframe?url=" + encodeURIComponent(url)}
  title="Embed"
  sandbox="allow-scripts allow-same-origin allow-popups allow-presentation"
  allow="fullscreen;accelerometer;clipboard-write;encrypted-media;gyroscope;picture-in-picture;web-share;"
></iframe>

<style>
  iframe {
    width: 100%;
    border: none;
  }
</style>
