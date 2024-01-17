<script lang="ts">
	import { Loader, SplitControl } from "@hyvor/design/components";
	import OnlyPrimaryVariant from "./OnlyPrimaryVariant.svelte";
	import UnsavedTag from "./UnsavedTag.svelte";
	import { postStore, postVariantStore, updatePostStore } from "../../../postStore";
	import ImageSetting from "../../../../settings/@components/ImageSetting.svelte";
	import { updatePost } from "../../../postActions";

    let loaderState : 'none' | 'loading' | 'success' | 'error' = 'none';

    function handleChange(e: CustomEvent<string | null>) {
        const url  = e.detail;
        updatePostStore({featured_image_url: url});

        loaderState = 'loading';

        if ($postVariantStore.status !== 'published') {
            updatePost({featured_image_url: url})
                .then(() => loaderState = 'success')
                .catch(() => loaderState = 'error');
        }
    }
</script>


<OnlyPrimaryVariant>

    <SplitControl>
        <span slot="label">
            Cover Image
            
            <UnsavedTag 
                show={$postStore.featured_image_url !== $postStore.featured_image_url}
                loaderState={loaderState}
            />

        </span>
        
        <ImageSetting 
            src={$postStore.featured_image_url}
            on:change={handleChange}
        />

    </SplitControl>

</OnlyPrimaryVariant>