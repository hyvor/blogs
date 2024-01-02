<script lang="ts">
	import { postEditingStatusStore, postVariantStore } from "../../../postStore";
	import EditorTop from "./EditorTop/EditorTop.svelte";
    import Prosemirror from "./Prosemirror.svelte";

    let content: string | null;

    $: {

        content = $postVariantStore.status === 'draft' ?
            $postVariantStore.content :
            (
                $postEditingStatusStore.isEditingPublished ?
                    $postVariantStore.content_unsaved :
                    $postVariantStore.content
            )

    }

</script>

<div class="editor hds-box">
    <EditorTop />
    <Prosemirror value={content} />
</div>

<style>
    
    .editor {
        position: relative;
    }

</style>