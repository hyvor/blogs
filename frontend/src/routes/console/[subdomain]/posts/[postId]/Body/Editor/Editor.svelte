<script lang="ts">
	import type { EditorView } from "prosemirror-view";
	import { postCurrentContentKey, postCurrentContentStore, postEditingStatusStore, postVariantStore, updatePostEditingStatusValue, updatePostVariantStore } from "../../../postStore";
	import EditorTop from "./EditorTop/EditorTop.svelte";
    import Prosemirror from "./Prosemirror.svelte";

    $: uniqueKey = `${$postVariantStore.id}` +
        `-lang-${$postEditingStatusStore.languageId}`;

    function handleChange(e: CustomEvent<string>) {
        updatePostVariantStore({
            [$postCurrentContentKey]: e.detail
        });
    }

    function handleView(e: CustomEvent<EditorView>) {
        updatePostEditingStatusValue('editorView', e.detail);
    }

</script>

<div class="editor hds-box">
    <EditorTop />

    {#key uniqueKey}
        <Prosemirror 
            value={$postCurrentContentStore} 
            on:change={handleChange}    
            on:view={handleView}
        />
    {/key}
</div>

<style>
    
    .editor {
        position: relative;
    }

</style>