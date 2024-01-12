<script lang="ts">
	import { Button, Loader, TextInput, toast } from "@hyvor/design/components";
	import { IconArrowReturnLeft } from "@hyvor/icons";
	import { createEventDispatcher, onMount } from "svelte";
	import type { SelectedImage } from "./image-uploader";
	import { isValidUrl } from "../../helper/is-valid-url";

    export let isUploading = false;

    let inputEl: HTMLInputElement;
    let byUrlInputEl: HTMLInputElement;

    let byUrl = '';
    let isDragging = false;

    function getCtrl() {
        const platform = (navigator as any)?.userAgentData?.platform || navigator?.platform || 'unknown'
        return platform.match(/mac/i) ? '⌘' : 'Ctrl';
    }

    const dispatch = createEventDispatcher<{select: SelectedImage}>();

    function handleFetch() {
        isUploading = true;

        fetch(byUrl)
            .then(res => res.blob())
            .then(blob => {

                // check if valid image
                if (blob.type.indexOf('image') !== 0) {
                    toast.error('The URL is not an image');
                    return;
                }

                const url = URL.createObjectURL(blob);
                dispatch('select', {
                    url,
                    from: 'upload',
                    upload: 'url'
                });
            })
            .catch(err => {
                toast.error('Failed to fetch image');
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
            if (item.type.indexOf('image') === 0) {
                const blob = item.getAsFile();
                if (!blob) continue;
                const url = URL.createObjectURL(blob);
                dispatch('select', {
                    url,
                    from: 'upload',
                    upload: 'paste'
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
        const url = getUrlFromFiles(files);
        if (!url) return;

        dispatch('select', {
            url,
            from: 'upload',
            upload: 'dnd'
        });
    }

    function handleUploadClick() {
        inputEl.click();
    }

    function handleInputChange(e: any) {
        const url = getUrlFromFiles(e.target.files);
        if (!url) return;
        dispatch('select', {
            url,
            from: 'upload',
            upload: 'browse'
        });
    }

    function getUrlFromFiles(files: FileList | null) : string | null {
        if (!files || files.length === 0) {
            toast.error('No files selected');
            return null;
        }
        const file = files[0];
        if (!file) {
            toast.error('No files selected');
            return null;
        }

        const url = URL.createObjectURL(file);
        
        return url;
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
        accept="image/*"
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

        <div class="by-url-wrap">
            <div class="title">
                or, Upload by URL
            </div>

            <div class="input-button">
                <TextInput
                    block 
                    placeholder="Enter image URL"
                    bind:value={byUrl}
                    on:keyup={e => e.key === 'Enter' && handleFetch()}
                    bind:input={byUrlInputEl}
                />
                <Button
                    disabled={byUrl.trim() === ''}
                    on:click={handleFetch}
                >
                    Fetch <IconArrowReturnLeft slot="end" />
                </Button>
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