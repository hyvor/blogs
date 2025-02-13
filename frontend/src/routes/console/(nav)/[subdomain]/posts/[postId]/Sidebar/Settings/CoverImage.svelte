<script lang="ts">
	import { Loader, SplitControl } from "@hyvor/design/components";
	import OnlyPrimaryVariant from "./OnlyPrimaryVariant.svelte";
	import UnsavedTag from "./UnsavedTag.svelte";
	import { postOriginalStore, postStore, postVariantStore, updatePostStore } from "../../../postStore";
	import ImageSetting from "../../../../settings/@components/ImageSetting.svelte";
	import { updatePost } from "../../../postActions";

    let loaderState : 'none' | 'loading' | 'success' | 'error' = $state('none');

    function handleChange(e: CustomEvent<string | null>) {
        const url  = e.detail;
        updatePostStore({featured_image_url: url});


        if ($postVariantStore.status !== 'published') {
            loaderState = 'loading';
            
            updatePost({featured_image_url: url})
                .then(() => loaderState = 'success')
                .catch(() => loaderState = 'error');
        }
    }
</script>


<OnlyPrimaryVariant>

    <SplitControl>
        {#snippet label()}
                <span >
                Cover Image
                
                <UnsavedTag 
                    show={$postStore.featured_image_url !== $postOriginalStore.featured_image_url}
                    loaderState={loaderState}
                />
            </span>
            {/snippet}
        
        <ImageSetting 
            src={$postStore.featured_image_url}
            on:change={handleChange}
        />

    </SplitControl>

</OnlyPrimaryVariant>