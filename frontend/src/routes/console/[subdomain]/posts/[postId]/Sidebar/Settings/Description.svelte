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

        const desc = (e.target.value as string).trim();
        
        if (desc === $postOriginalVariantStore.description)
            return;

        if ($postVariantStore.status !== 'published') {

            loaderState = 'loading';

            updatePostVariant({description: desc})
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
        <UnsavedTag 
            show={$postVariantStore.description !== $postOriginalVariantStore.description}
            loaderState={loaderState}
        />
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