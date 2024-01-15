<script lang="ts">
	import type { EditorView } from "prosemirror-view";
	import { postCurrentContentKey, postCurrentContentStore, postEditingStatusStore, postOriginalVariantStore, postVariantStore, updatePostEditingStatusValue, updatePostVariantStore } from "../../../postStore";
	import EditorTop from "./EditorTop/EditorTop.svelte";
    import Prosemirror from "./Prosemirror.svelte";
	import PublishedOverlay from "./PublishedOverlay.svelte";
	import type { PostVariant } from "../../../../../lib/types";

    $: uniqueKey = `${$postVariantStore.id}` +
        `-lang-${$postEditingStatusStore.languageId}`;

    function handleChange(e: CustomEvent<string>) {

        const key = $postCurrentContentKey as 'content' | 'content_unsaved';

        const updates = {
            [key]: e.detail
        } as Partial<PostVariant>;
       
        if (key === 'content_unsaved') {
            updates.content = $postVariantStore.content;
        }

        updatePostVariantStore(updates);
    }

    function handleView(e: CustomEvent<EditorView>) {
        updatePostEditingStatusValue('editorView', e.detail);
    }

</script>

<div class="editor hds-box">
    <EditorTop />

    {#key uniqueKey}
        <div class="wrap">
            <Prosemirror 
                value={$postCurrentContentStore} 
                on:change={handleChange}
                on:view={handleView}
            />
            <PublishedOverlay />
        </div>
    {/key}
</div>

<style>
    
    .editor {
        position: relative;
    }
    .wrap {
        position: relative;
    }
</style>