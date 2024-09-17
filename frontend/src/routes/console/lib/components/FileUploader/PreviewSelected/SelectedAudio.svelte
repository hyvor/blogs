<script lang="ts">
    import Cropper from 'cropperjs';
    import 'cropperjs/dist/cropper.css';
	import Meta from "./Meta.svelte";
	import type { SelectedAudio } from "../audio-uploader";
	import { Button, Loader, Switch, toast } from "@hyvor/design/components";
	import { IconCheckAll, IconCloudUpload } from "@hyvor/icons";
	import byteFormatter from "../../../helper/byte-formatter";
	import { createEventDispatcher, onDestroy, onMount } from "svelte";
	import { uploadMedia } from "../../../../[subdomain]/tools/media/mediaActions";

    export let audio: SelectedAudio;
    
    const audioSrc: string = audio.src instanceof Blob ? URL.createObjectURL(audio.src) : audio.src;

    let audioEl : HTMLAudioElement;

    function getCanChangeUpload() {
        return audio.from === 'upload' && audio.upload?.originalSrc;
    }

    let shouldUpload = audio.from === 'upload';
    const canChangeUpload = getCanChangeUpload();

    const dispatch = createEventDispatcher<{select: SelectedAudio}>();

    let isUploading = false;

    function handleSelect(au: SelectedAudio = audio) {
        dispatch('select', au);
    }

    function handleUpload() {
        if (shouldUpload && audio.src instanceof Blob) {
            isUploading = true;
            uploadMedia(audio.src)
            .then(res => {
                    handleSelect({
                        ...audio,
                        src: res.url,
                    });
                })
                .catch(err => {
                    toast.error(err.message || 'Failed to upload audio');
                })
                .finally(() => {
                    isUploading = false;
                });
        }
        else {
            handleSelect();
        }
    }

</script>



<div class="selected-audio">

    {#if isUploading}
        <Loader full>
            Uploading...
        </Loader>
    {:else}

        <div class="audio-wrap">
            <audio
                src={audioSrc}
                bind:this={audioEl}
                controls
            />
        </div>

        <div class="top-bar">
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

    {/if}

</div>


<style>
    .selected-audio {
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

    .audio-wrap {
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