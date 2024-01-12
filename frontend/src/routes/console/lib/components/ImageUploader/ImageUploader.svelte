<script lang="ts">
	import type { SelectedImage as SelectedImageType } from './image-uploader.ts';
	import { Button, Modal, TabNav, TabNavItem } from "@hyvor/design/components";
	import { IconCardImage, IconCaretLeft, IconCloudUpload } from "@hyvor/icons";
	import TabUpload from "./TabUpload.svelte";
	import SelectedImage from "./SelectedImage.svelte";
	import ExcalidrawIcon from "./Excalidraw/ExcalidrawIcon.svelte";
	import Excalidraw from "./Excalidraw/Excalidraw.svelte";
	import Unsplash from "./Unsplash/Unsplash.svelte";
	import Media from "./Media/Media.svelte";
    
    let tab = 'upload';

    let backImage: null | SelectedImageType = null;
    let selectedImage: null | SelectedImageType = null;

    /* selectedImage = {
        url: 'https://fengyuanchen.github.io/cropperjs/images/picture.jpg',
        from: 'upload'
    }; */

    function handleSelect(e: CustomEvent<SelectedImageType>) {
        selectedImage = e.detail;
        backImage = null;
    }

    function handleBack() {
        backImage = selectedImage;
        selectedImage = null;
    }

</script>

<div class="image-uploader">
    <Modal 
        show={true} 
        size="large"
        closeOnEscape={false}
        closeOnOutsideClick={false}
    >

        <div slot="title">

            {#if selectedImage}

                <Button 
                    on:click={handleBack}
                    color="input"
                >
                    <IconCaretLeft slot="start" va />
                    Back
                </Button>

            {:else}

                <TabNav bind:active={tab}>
                    <TabNavItem name="upload">
                        <IconCloudUpload slot="start" />
                        Upload
                    </TabNavItem>
                    <TabNavItem name="media">
                        <IconCardImage slot="start" />
                        Media Library
                    </TabNavItem>
                    <TabNavItem name="unsplash">
                        <svg 
                            role="img" 
                            width="1em" 
                            height="1em" 
                            fill="currentColor" 
                            viewBox="0 0 24 24" 
                            xmlns="http://www.w3.org/2000/svg"
                            slot="start"
                        ><path d="M7.5 6.75V0h9v6.75h-9zm9 3.75H24V24H0V10.5h7.5v6.75h9V10.5z"/></svg>
                        Unsplash
                    </TabNavItem>
                    <TabNavItem name="excalidraw">
                        <ExcalidrawIcon slot="start" />
                        Excalidraw
                    </TabNavItem>
                </TabNav>

            {/if}

        </div>


        <div 
            class="body"
            style:position={selectedImage ? 'relative' : undefined}
        >

            {#if tab === 'upload'}
                <TabUpload on:select={handleSelect} />
            {:else if tab === 'media'}
                <Media on:select={handleSelect} />
            {:else if tab === 'unsplash'}
                <Unsplash on:select={handleSelect} />
            {:else if tab === 'excalidraw'}
                <Excalidraw on:select={handleSelect} />
            {/if}

            {#if selectedImage}
                <SelectedImage url={selectedImage.url} />
            {/if}

        </div>

    </Modal>
</div>

<style lang="scss">

    .image-uploader :global(.wrap) {
        z-index: 1000!important;
    }

    .image-uploader :global(.inner) {
        height: 100%;
        width: 1100px!important;
        display: flex;
        flex-direction: column;
        :global(> .content) {
            flex: 1;
            padding-top: 0;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
    }

    .body {
        flex: 1;
        min-height: 0;
    }

</style>