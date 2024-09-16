<script lang="ts">
	import { Button, Loader, TextInput, toast } from "@hyvor/design/components";
	import { IconArrowReturnLeft } from "@hyvor/icons";
	import { createEventDispatcher, onMount } from "svelte";
	import { VALID_MIME_TYPES_AUDIO, type SelectedAudio, VALID_MIME_TYPES_NAMES_AUDIO } from "./image-uploader";
	import { isValidUrl } from "../../helper/is-valid-url";
	import { getConfig } from "../../config";
	import byteFormatter from "../../helper/byte-formatter";

    export let isUploading = false;

    let inputEl: HTMLInputElement;
    let byUrlInputEl: HTMLInputElement;

    let byUrl = '';
    let isDragging = false;

    function getCtrl() {
        const platform = (navigator as any)?.userAgentData?.platform || navigator?.platform || 'unknown'
        return platform.match(/mac/i) ? '⌘' : 'Ctrl';
    }

    const dispatch = createEventDispatcher<{select: SelectedAudio}>();

    function handleFetch() {
        isUploading = true;

        fetch(byUrl)
            .then(res => res.blob())
            .then(blob => {
                // check if valid image
                if (blob.type.indexOf('audio') !== 0) {
                    toast.error('The URL is not an audio');
                    return;
                }

                dispatch('select', {
                    src: blob,
                    from: 'upload',
                    upload: {
                        type: 'url',
                        originalSrc: byUrl
                    }
                });
            })
            .catch(err => {
                toast.error('Failed to fetch audio');
            })
            .finally(() => {
                isUploading = false;
            });
    }

    function handlePaste(e: ClipboardEvent) {

        const text = e.clipboardData?.getData('text/plain') || '';

        if (isValidUrl(text) && (e.target as HTMLElement).tagName !== 'INPUT') {
            byUrl = text;
            handleFetch();
            return;
        }

        const items = e.clipboardData?.items;
        if (!items) return;

        for (let i = 0; i < items.length; i++) {
            const item = items[i]!;
            if (item.type.indexOf('audio') === 0) {
                const blob = item.getAsFile();
                if (!blob) continue;
                dispatch('select', {
                    src: blob,
                    from: 'upload',
                    upload: {type: 'paste'}
                });
                break;
            }
        }

    }

    function handleDragEnter(e: DragEvent) {
        e.preventDefault();
        e.stopPropagation();
        isDragging = true;
    }

    function handleDragLeave(e: DragEvent) {
        e.preventDefault();
        e.stopPropagation();
        isDragging = false;
    }

    function handleDragDrop(e: DragEvent) {
        e.preventDefault();
        e.stopPropagation();

        isDragging = false;

        if (!e.dataTransfer) return;
        const files = e.dataTransfer.files;
        const file = getFileFromFiles(files);
        if (!file) return;

        dispatch('select', {
            src: file,
            from: 'upload',
            upload: {type: 'dnd'}
        });
    }

    function handleUploadClick() {
        inputEl.click();
    }

    function handleInputChange(e: any) {
        const file = getFileFromFiles(e.target.files);
        if (!file) return;
        dispatch('select', {
            src: file,
            from: 'upload',
            upload: {type: 'browse'}
        });
    }

    function getFileFromFiles(files: FileList | null) : File | null {
        if (!files || files.length === 0) {
            toast.error('No files selected');
            return null;
        }
        const file = files[0];
        if (!file) {
            toast.error('No files selected');
            return null;
        }

        const max = getConfig().limits.max_upload_size;
        if (file.size > max) {
            toast.error('File size exceeds the limit of ' + byteFormatter(max));
            return null;
        }

        if (!VALID_MIME_TYPES_AUDIO.includes(file.type)) {
            const names = VALID_MIME_TYPES_NAMES_AUDIO.join(', ').toUpperCase();
            toast.error(`Only ${names} audio are allowed`);
            return null;
        }

        // const url = URL.createObjectURL(file);
        
        return file;
    }

    onMount(() => {
        byUrl && byUrlInputEl && byUrlInputEl.focus();
    })

</script>

<svelte:window 
    on:paste={handlePaste}
    on:dragenter={handleDragEnter}
    on:dragover={handleDragEnter}
    on:dragleave={handleDragLeave}
    on:dragexit={handleDragLeave}
/>

<div class="tab">

    <input
        type="file"
        accept="audio/*"
        style="display:none"
        bind:this={inputEl}
        on:change={handleInputChange}
    />

    {#if isUploading}
        <Loader full />
    {:else}

        <div class="upload-wrap">
            <div 
                class="upload-area"
                on:click={handleUploadClick}
                on:drop={handleDragDrop}
                role="button"
                tabindex="0"
                on:keyup={e => e.key === 'Enter' && handleUploadClick()}
            >
                {#if isDragging}
                    Drop here!
                {:else}
                    Drag and drop, paste ({getCtrl()} + v), or click to upload
                {/if}
            </div>
        </div>

        <!-- <div
            class="upload-area" 
            onClick={() => inputRef.current && inputRef.current.click()}
            ref={uploadAreaRef}
        >
            {
                isDragging ?
                "Drop here!" :
                "Drag and drop, paste, or click to upload"
            }
        </div> -->
    {/if}

</div>

<style lang="scss">

    .tab {
        height: 100%;
        display: flex;
        flex-direction: column;
        padding-bottom: 15px;
    }

    .upload-wrap {
        flex: 1;
        width: 100%;
        height: 100%;
        .upload-area {
            background-color: var(--input);
            width: 100%;
            height: 100%;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: var(--text-light);
            transition: .2s box-shadow;
            cursor: pointer;
            &:hover {
                box-shadow: 0 0 0 2px var(--accent-light);
            }
        }
    }

    .by-url-wrap {
        margin-top: 15px;
        .title {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-light);
            margin-bottom: 10px;
            padding-left: 5px;
            text-align: center;
        }
        .input-button {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    }
</style>