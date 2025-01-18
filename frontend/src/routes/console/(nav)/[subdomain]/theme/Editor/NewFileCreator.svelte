<script lang="ts">
	import { Button, toast } from "@hyvor/design/components";
	import type { ThemeFolder } from "../../../../lib/types";
	import { IconCloudUpload, IconPlus } from "@hyvor/icons";
	import CreateEditModal from "./Modals/CreateEditModal.svelte";
	import { getConfig } from "../../../../lib/config";
	import byteFormatter from "../../../../lib/helper/byte-formatter";
	import { createFile } from "../themeActions";
	import { addThemeFileToStore, selectedThemeFileIdStore } from "../themeStore";

    interface Props {
        folder: ThemeFolder;
    }

    let { folder }: Props = $props();

    let uploadInput: HTMLInputElement = $state();
    
    let isCreating = $state(false);

    function handleUpload() {
        const files = uploadInput.files;
        const file = files?.[0] || null;
        if (!file) {
            return toast.error('Please select a file to upload');
        }

        const maxSize = getConfig().limits.max_asset_file_size;
        if (file.size > maxSize) {
            return toast.error('File too large. Max size is ' + byteFormatter(maxSize));
        }

        const toastId = toast.loading('Uploading file...');

        createFile(
            folder, 
            file.name, 
            file
        ).then(res => {
            toast.success('File uploaded', {id: toastId});
            addThemeFileToStore(res);
            selectedThemeFileIdStore.set(res.id);
        }).catch(err => {
            toast.error(err.message || 'Unable to upload file', {id: toastId});
        });

    }

    function handleUploadClick() {
        uploadInput.click();
    }

</script>


<div class="file-creator">

    <input
        type="file"
        bind:this={uploadInput}
        style="display: none;"
        onchange={handleUpload}
    />

    <Button 
        size="x-small" 
        variant="invisible"
        on:click={() => isCreating = true}
    >
        {#snippet start()}
                <IconPlus size={11}  />
            {/snippet}
        New
    </Button>

    {#if folder === 'assets'}
        <Button 
            size="x-small" 
            variant="invisible"
            on:click={handleUploadClick}
        >
            {#snippet start()}
                        <IconCloudUpload size={11}  />
                    {/snippet}
            Upload
        </Button>
    {/if}

</div>

{#if isCreating}
    <CreateEditModal file={{id: null, name: '', folder:folder}} bind:open={isCreating} />
{/if}

<style>
    .file-creator {
        margin-top: 3px;
        margin-bottom: 4px;
    }
</style>