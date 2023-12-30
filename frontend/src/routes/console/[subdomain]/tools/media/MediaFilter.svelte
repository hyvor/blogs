<script lang="ts">
	import { ActionList, ActionListItem, Button, Dropdown, Text, TextInput } from "@hyvor/design/components";
	import { IconCaretDown } from "@hyvor/icons";
	import { getExtensionsByFileType, type FileType } from "./mediaActions";
	import { createEventDispatcher } from "svelte";

    let type: FileType = 'all';
    let showFileTypesDropdown = false;
    let customExtension = '';
    let search = '';

    const dispatch = createEventDispatcher();

    let fileTypes = [
        { name: 'All', value: 'all', extensions: '' },
        { name: 'Images', value: 'images', extensions: 'jpg, png...' },
        { name: 'Videos', value: 'videos', extensions: 'mp4, avi...' },
        { name: 'Documents', value: 'documents', extensions: 'pdf, doc...' },
        { name: 'Audio', value: 'audio', extensions: 'mp3, wav...' },
        { name: 'Archives', value: 'archives', extensions: 'zip, rar...' },
        { name: 'Custom Extension', value: 'custom', extensions: 'You choose' }
    ] as {name: string, value: FileType, extensions: string}[];

    $: selectedFileName = fileTypes.find(f => f.value === type)!.name;

    function closeAndDispatch() {
        showFileTypesDropdown = false;
        dispatchChange();
    }

    function dispatchChange() {
        const customExtensionsArray = customExtension.split(',')
            .map(ext => ext.trim())
            .filter(ext => ext.length > 0);
        
        const searchVal = search.trim() ? search.trim() : null;

        dispatch('change', {
            extensions: getExtensionsByFileType(type, customExtensionsArray),
            search: searchVal
        });
    }

    function selectFileType(fileType: FileType) {
        type = fileType;

        if (fileType !== 'custom') {
            closeAndDispatch();
        }
    }

    function handleChooseCustomExt() {
        closeAndDispatch();
    }

    let searchTimeout : null | ReturnType<typeof setTimeout> = null;

    function handleSearchInput() {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        searchTimeout = setTimeout(dispatchChange, 400);
    }
    
</script>


<div class="toolbar">

    <Dropdown width={300} bind:show={showFileTypesDropdown}>
        <Button slot="trigger" color="light">
            <Text small light slot="start">File type</Text>
            { selectedFileName }

            {#if type === 'custom' && customExtension}
                - { customExtension }
            {/if}

            <IconCaretDown slot="end" size={12} />
        </Button>

        <ActionList slot="content" selection="single">
            {#each fileTypes as f (f.value)}
                <ActionListItem 
                    on:select={() => selectFileType(f.value)}
                    selected={type === f.value}
                >
                    { f.name }

                    <Text small light slot="end">
                        { f.extensions }
                    </Text>
                </ActionListItem>
            {/each}

            {#if type === 'custom'}
                <div class="custom-ext">
                    <TextInput 
                        bind:value={customExtension}
                        size="x-small"
                        autofocus
                        placeholder="svg, gif..."
                    />
                    <Button size="small" on:click={handleChooseCustomExt}>
                        Choose
                    </Button>
                </div>
            {/if}
                
        </ActionList>

    </Dropdown>

    <TextInput 
        placeholder="Search..." 
        style="width: 130px"
        bind:value={search}
        on:input={handleSearchInput}
    />

</div>

<style>

    .custom-ext {
        display: flex;
        align-items: center;
        gap: 5px;
        padding-top: 5px;
        padding-bottom: 10px;
        padding-left: 40px;
    }

    .toolbar {
        display: flex;
        gap: 8px;
    }

</style>