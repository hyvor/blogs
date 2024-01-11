<script lang="ts">
	import { postCurrentContentKey, postCurrentContentStore, postEditingStatusStore, postVariantStore, updatePostVariantStore } from "../../../postStore";
	import EditorTop from "./EditorTop/EditorTop.svelte";
    import Prosemirror from "./Prosemirror.svelte";

    $: uniqueKey = `${$postVariantStore.id}` +
        `-lang-${$postEditingStatusStore.languageId}`;

    function handleChange(e: CustomEvent<string>) {
        updatePostVariantStore({
            [$postCurrentContentKey]: e.detail
        });
    }

</script>

<div class="editor hds-box">
    <EditorTop />

    {#key uniqueKey}
        <Prosemirror 
            value={$postCurrentContentStore} 
            on:change={handleChange}    
        />
    {/key}
</div>

<style>
    
    .editor {
        position: relative;
    }

</style>