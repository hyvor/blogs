<script lang="ts">
	import { Button, ButtonGroup } from "@hyvor/design/components";
	import ImageUploader from "../../../lib/components/FileUploader/ImageUploader.svelte";
	import type { SelectedImage } from "../../../lib/components/FileUploader/image-uploader";
	import { createEventDispatcher } from "svelte";

    export let src: string | null = null;
    export let uploadText = "Upload";

    let isUploading = false;

    const dispatch = createEventDispatcher<{change: string | null}>();
    
    function handleSelect(e: CustomEvent<SelectedImage>) {
        isUploading = false;
        dispatch('change', e.detail.url as string);
    }

    function handleRemove() {
        dispatch('change', null);
    }

</script>

{#if !src}
    <Button size="small" on:click={() => isUploading = true}>
        {uploadText}
    </Button>
{:else}

    <div class="img-wrap">
        <img src={src} alt="Uploaded" />
    </div>

    <div class="buttons">
        <ButtonGroup>
            <Button on:click={() => isUploading = true} size="x-small" variant="fill-light">
                Change
            </Button>
            <Button on:click={handleRemove} size="x-small" color="red" variant="fill-light">
                Remove
            </Button>
        </ButtonGroup>
    </div>

{/if}

{#if isUploading}
    <ImageUploader 
        on:select={handleSelect}
        bind:show={isUploading}
    />
{/if}

<style>
    img {
        max-width: 250px;
        max-height: 250px;
        border-radius: 5px;
    }
    .buttons {
        margin-top: 5px;
    }
</style>