<script lang="ts">
	import { onMount } from "svelte";
	import { setInnerHTMLWithScripts } from "../../../../../../../../lib/helper/setInnerHtmlWithScripts";

    export let html: string | null;

    let el: HTMLDivElement;

    onMount(() => {
        setInnerHTMLWithScripts(el, html || '');
    });
</script>

<div bind:this={el} />