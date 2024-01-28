<script lang="ts">
	import { Button, Caption, FormControl, IconButton, Loader, Modal, TextInput, Tooltip } from "@hyvor/design/components";
	import { IconPencilFill, IconTrash } from "@hyvor/icons";
	import { selectedThemeFileOriginalStore, selectedThemeFileStore } from "../themeStore";
	import { onMount } from "svelte";
	import EditModal from "./Modals/CreateEditModal.svelte";
	import DeleteModal from "./Modals/DeleteModal.svelte";
	import { fileSavingState, saveCurrentFile } from "../theme";

    let isUpdating = false;
    let isDeleting = false;

    $: currentFile = $selectedThemeFileStore!;
    $: currentOriginalFile = $selectedThemeFileOriginalStore;
    $: contentChanged = currentFile.content !== currentOriginalFile?.content;

    let updatingFileName = '';

    onMount(() => {
        updatingFileName = currentFile.name;
    })

    function handleSave() {
        saveCurrentFile();
    }
</script>

<div class="editor-top-bar">

    <div class="left">

        <span class="name">
            {#if currentFile.folder}<span class="folder">{currentFile.folder}/</span>{/if}{currentFile.name}
        </span>

        <span class="buttons">

            <Tooltip text="Edit file name" position="bottom">
                <IconButton size={22} on:click={() => isUpdating = true}>
                    <IconPencilFill size={10} />
                </IconButton>
            </Tooltip>

            <Tooltip text="Delete file" position="bottom">
                <IconButton size={22} on:click={() => isDeleting = true}>
                    <IconTrash size={10} />
                </IconButton>
            </Tooltip>
        </span>

    </div>

    <div class="right">

        <Loader 
            bind:state={$fileSavingState}
            size={14}
            style="margin-right:8px;"
        />

        <Button 
            disabled={!contentChanged || $fileSavingState === 'loading'}
            on:click={handleSave}
        >
            { $fileSavingState === 'loading' ? 'Saving' : contentChanged ? 'Save' : 'Saved' }
        </Button>

    </div>

</div>

{#key currentFile.id}
    <EditModal bind:open={isUpdating} file={currentFile} />
    <DeleteModal bind:open={isDeleting} file={currentFile} />
{/key}

<style lang="scss">

    .editor-top-bar {
        display: flex;
        color: var(--text-light);
        font-weight: 600;
        padding: 10px 25px;
        border-bottom: 1px solid var(--accent-light-mid);
        position: relative;
        align-items: center;

        .folder {
            font-weight: normal;
        }

        .left {
            flex: 1;
        }

        .buttons {
            margin-left: 6px;
        }

        /* .config-yaml-switch {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            .switch-title {
                margin-right: 5px;
            }
        } */
    }

</style>