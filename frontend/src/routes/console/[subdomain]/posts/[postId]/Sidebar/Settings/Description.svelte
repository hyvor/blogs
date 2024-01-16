<script lang="ts">
	import { Loader, SplitControl, Textarea } from "@hyvor/design/components";
	import { postOriginalVariantStore, postVariantStore, updatePostVariantStore } from "../../../postStore";
	import UnsavedTag from "./UnsavedTag.svelte";
	import { updatePostVariant } from "../../../postActions";

    function handleInput(e: any) {
        updatePostVariantStore({description: e.target.value});
    }

    let loaderState : 'none' | 'loading' | 'success' | 'error' = 'none';

    function handleBlur(e: any) {

        if ($postVariantStore.status !== 'published') {

            loaderState = 'loading';

            updatePostVariant({description: e.target.value})
                .then(() => {
                    loaderState = 'success';
                })
                .catch(err => {
                    loaderState = 'error';
                })
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
    >
        <span slot="end">
            <Loader
                size="small" 
                colorTrack="var(--input)"
                state={loaderState}
            />
        </span>
    </Textarea>
</SplitControl>