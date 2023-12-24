<script lang="ts">
	import { postEditingStatusStore, postVariantStore } from "../../../../../lib/stores/postStore";
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

<div class="editor">
    <Prosemirror value={content} />
</div>

<style>
    
    .editor {
        position: relative;
    }

</style>