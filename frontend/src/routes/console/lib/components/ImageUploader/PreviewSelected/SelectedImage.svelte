<script lang="ts">
    import Cropper from 'cropperjs';
    import 'cropperjs/dist/cropper.css';
	import Meta from "./Meta.svelte";
	import type { SelectedImage } from "../image-uploader";
	import { Button, Switch } from "@hyvor/design/components";
	import { IconCheckAll, IconCloudUpload } from "@hyvor/icons";
	import byteFormatter from "../../../helper/byte-formatter";
	import { createEventDispatcher, onMount } from "svelte";

    export let image: SelectedImage;

    let imgEl : HTMLImageElement;
    let cropper : Cropper;

    let width = 0;
    let height = 0;

    function getShouldUpload() {
        if (image.from === 'excalidraw' || image.from == 'upload')
            return true;
        return false;
    }

    function getCanChangeUpload() {
        return image.from === 'upload' && image.upload?.type === 'url' && image.upload?.originalUrl;
    }

    function getHosting() {

        if (image.from === 'upload') {
            if (image.upload?.type === 'url' && image.upload?.originalUrl) {
                const originalUrl = image.upload.originalUrl!;
                const domain = new URL(originalUrl).hostname;
                return `External (${domain})`;
            }
        }

        if (image.from === 'unsplash') {
            return 'Unsplash';
        }

        if (image.from === 'media') {
            return 'Media Library';
        }

        return null;
    }

    let shouldUpload = getShouldUpload();
    const canChangeUpload = getCanChangeUpload();
    const hosting = getHosting();

    function handleImageLoad() {

        width = imgEl.naturalWidth;
        height = imgEl.naturalHeight;

        /* cropper = new Cropper(imgEl, {
            viewMode: 3,
            aspectRatio: 16 / 9,
            crop(event) {
                console.log(event.detail.x);
                console.log(event.detail.y);
                console.log(event.detail.width);
                console.log(event.detail.height);
                console.log(event.detail.rotate);
                console.log(event.detail.scaleX);
                console.log(event.detail.scaleY);
            },
        }); */
    }

    let editing : null | 'crop' = null;

    function tryGetSize() {
        fetch(image.url)
            .then(res => res.blob())
            .then(blob => {
                const size = blob.size;
                image = {
                    ...image,
                    size,
                }
            })
            .catch(err => {
                console.error(err);
            });
    }

    const dispatch = createEventDispatcher<{select: SelectedImage}>();

    function handleSelect() {
        dispatch('select', image);
    }

    function handleUpload() {
        if (shouldUpload) {
            
        } else {
            handleSelect();
        }
    }


    onMount(async () => {

        if (image.size === null) {
            tryGetSize();
        }

    })

</script>


<div class="selected-image">

    <div class="img-wrap">
        <img 
            src={image.url} 
            alt="Editing"
            bind:this={imgEl}
            on:load={handleImageLoad}
        />
    </div>

    <div class="top-bar">
        <div class="meta">
            <Meta name="Dimensions (px)">
                {width} x {height}
            </Meta>
            <Meta name="File Size">
                {#if image.size !== null}
                    { byteFormatter(image.size) }
                {:else}
                    Unknown
                {/if}
            </Meta>
            {#if image.name}
                <Meta name="Name">
                    { image.name }
                </Meta>
            {/if}
            {#if hosting}
                <Meta name="Hosting">
                    {hosting}
                </Meta>
            {/if}
        </div>
        <div class="upload-switch">
            Upload to Media Library
            <Switch 
                bind:checked={shouldUpload}
                disabled={!canChangeUpload}
            />
        </div>
    </div>

    <div class="footer">

        <Button on:click={handleUpload}>
            { shouldUpload ? 'Upload' : 'Select' }
            <svelte:fragment slot="end">
                {#if shouldUpload}
                    <IconCloudUpload />
                {:else}
                    <IconCheckAll />
                {/if}
            </svelte:fragment>
        </Button>

    </div>

</div>


<style>
    .selected-image {
        position: absolute;
        z-index: 1000000;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--box-background);
        display: flex;
        flex-direction: column;
    }

    .top-bar {
        padding: 10px 25px;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        margin: 0 -25px;
        display: flex;
        align-items: center;
    }

    .meta {
        display: flex;
        flex: 1;
    }

    .img-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 25px;
        flex: 1;
        min-height: 0;
        min-width: 0;
        margin-bottom: 20px;
    }
    .img-wrap :global(img) {
        display: block;
        max-width: 100%;
    }
    img {
        max-width: 100%;
        max-height: 100%;
    }

    .upload-switch {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }

    .footer {
        padding: 5px 25px;
        padding-top: 15px;
        text-align: center;
    }
</style>