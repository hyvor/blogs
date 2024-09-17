<script lang="ts">
	import type { SelectedAudio as SelectedAudioType } from './audio-uploader.js';
	import { Button, Modal, TabNav, TabNavItem } from "@hyvor/design/components";
	import { IconCardImage, IconCaretLeft, IconCloudUpload } from "@hyvor/icons";
	import Media from "./Media/Media.svelte";
	import { createEventDispatcher } from "svelte";
	import SelectedAudio from './PreviewSelected/SelectedAudio.svelte';
	import TabUpload from './TabUpload.svelte';
    
    let tab = 'upload';

    export let show = true;
    let backAudio: null | SelectedAudioType = null;
    let selectedAudio: null | SelectedAudioType = null;

    function handleSelect(e: CustomEvent<SelectedAudioType>) {
        selectedAudio = e.detail;
        backAudio = null;
    }

    function handleBack() {
        backAudio = selectedAudio;
        selectedAudio = null;
    }


    const dispatch = createEventDispatcher<{
        select: SelectedAudioType,
        close: undefined
    }>();

    $: if (!show) {
        dispatch('close');
    }


    function handleFinish(e: CustomEvent<SelectedAudioType>) {
        dispatch('select', e.detail);
        show = false;
    }

</script>

<div class="image-uploader">
    <Modal 
        bind:show={show} 
        size="large"
        closeOnEscape={false}
        closeOnOutsideClick={false}
    >

        <div slot="title">

            {#if selectedAudio}
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
                    
                </TabNav>

            {/if}

        </div>


        <div 
            class="body"
            style:position={selectedAudio ? 'relative' : undefined}
        >

            {#if tab === 'upload'}
                <TabUpload isAudio={true} on:audioSelect={handleSelect} />
            {:else if tab === 'media'}
                <Media isAudio={true} on:audioSelect={handleSelect} />
            {/if}

            {#if selectedAudio}
                <SelectedAudio 
                    audio={selectedAudio} 
                    on:select={handleFinish}
                />
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