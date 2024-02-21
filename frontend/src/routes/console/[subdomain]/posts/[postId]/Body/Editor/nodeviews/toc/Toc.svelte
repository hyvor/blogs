<script lang="ts">
	import type { EditorView } from "prosemirror-view";
	import { onMount } from "svelte";
	import { generateToc } from "./toc";
	import TocChildren from "./TocChildren.svelte";
	import TocLevels from "./TocLevels.svelte";

    export let view: EditorView;
    export let levels : number[] = [1,2,3,4];

    let doc = view.state.doc;

    $: toc = generateToc(doc, levels);

    function handleTransaction(e: any) {
        doc = e.detail.doc;
    }

    onMount(() => {
        document.addEventListener('prosemirror:transaction', handleTransaction);
        return () => {
            document.removeEventListener('prosemirror:transaction', handleTransaction);
        }
    })

</script>

<div class="wrap">
    <div class="title" role="heading" aria-level={2}>Table of Contents</div>
    <div class="toc-inner">
        <TocChildren children={toc} top />
    </div>
    <TocLevels bind:levels={levels} />
</div>

<style>
    .wrap {
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-top: 10px;
        white-space: initial;
    }
    .title {
        padding: 20px 25px;
        font-size: 20px;
        font-weight: 600;
        border-bottom: 1px solid var(--border);
    }
    .toc-inner {
        padding: 20px 25px;
    }
</style>