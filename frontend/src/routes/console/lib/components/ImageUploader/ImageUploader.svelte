<script lang="ts">
	import type { SelectedImage } from './image-uploader.ts';
	import { Button, Modal, TabNav, TabNavItem } from "@hyvor/design/components";
	import { IconCardImage, IconCaretLeft, IconCloudUpload } from "@hyvor/icons";
	import TabUpload from "./TabUpload.svelte";
	import ImageEditor from "./SelectedImage.svelte";
	import ExcalidrawIcon from "./Excalidraw/ExcalidrawIcon.svelte";
	import Excalidraw from "./Excalidraw/Excalidraw.svelte";
    
    let tab = 'upload';
    let selectedImage: null | SelectedImage = null;

    /* selectedImage = {
        url: 'https://fengyuanchen.github.io/cropperjs/images/picture.jpg',
        from: 'upload'
    }; */

    function handleSelect(e: CustomEvent<SelectedImage>) {
        selectedImage = e.detail;
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
                    on:click={() => selectedImage = null}
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
                    <TabNavItem name="Unsplash">
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

        {#if selectedImage}
            <ImageEditor url={selectedImage.url} />
        {:else}

            {#if tab === 'upload'}
                <TabUpload />
            {:else if tab === 'excalidraw'}
                <Excalidraw on:select={handleSelect} />
            {/if}

        {/if}

    </Modal>
</div>

<style lang="scss">

    .image-uploader :global(.wrap) {
        z-index: 1000!important;
    }

    .image-uploader :global(.inner) {
        height: calc(100%);
        width: 1100px!important;
        display: flex;
        flex-direction: column;
        :global(> .content) {
            flex: 1;
            padding-top: 10px;
            min-height: 0;
        }
    }

</style>