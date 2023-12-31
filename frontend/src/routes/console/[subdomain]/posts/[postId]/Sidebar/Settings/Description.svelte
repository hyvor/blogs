<script lang="ts">
	import { SplitControl, Textarea } from "@hyvor/design/components";
	import { postOriginalVariantStore, postVariantStore, updatePostVariantStore } from "../../../postStore";
	import UnsavedTag from "./UnsavedTag.svelte";

    function handleInput(e: any) {
        updatePostVariantStore({description: e.target.value});
    }

    function handleBlur(e: any) {

        if ($postVariantStore.status === 'draft') {
            // TODO: Add LoaderState 
            updatePostVariantStore({description: e.target.value});
        }

    }

</script>


<SplitControl>
    <span slot="label">
        Description
        
        {#if $postVariantStore.description !== $postOriginalVariantStore.description}
            <UnsavedTag />
        {/if}
    </span>
    <Textarea
        block
        rows={4}
        value={$postVariantStore.description || ''}
        on:input={handleInput}
        on:blur={handleBlur}
        maxlength={255}
    />
</SplitControl>