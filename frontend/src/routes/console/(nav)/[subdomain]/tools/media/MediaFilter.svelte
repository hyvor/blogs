<script lang="ts">
	import { ActionList, ActionListItem, Button, Dropdown, Text, TextInput } from "@hyvor/design/components";
	import { IconCaretDown } from "@hyvor/icons";
	import { getExtensionsByFileType, type FileType } from "./mediaActions";
	import { createEventDispatcher, onMount } from "svelte";

    interface Props {
        defaultType?: null | FileType;
        typeDisabled?: boolean;
    }

    let { defaultType = null, typeDisabled = false }: Props = $props();

    let type: FileType = $state(defaultType || 'all');
    let showFileTypesDropdown = $state(false);
    let customExtension = $state('');
    let search = $state('');

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

    let selectedFileName = $derived(fileTypes.find(f => f.value === type)!.name);

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

    onMount(dispatchChange);

</script>


<div class="toolbar">

    <Dropdown width={300} bind:show={showFileTypesDropdown}>
        {#snippet trigger()}
                <Button 
                 
                color="input"
                disabled={typeDisabled}
            >
                {#snippet start()}
                        <Text small light >File type</Text>
                    {/snippet}
                { selectedFileName }

                {#if type === 'custom' && customExtension}
                    - { customExtension }
                {/if}

                {#snippet end()}
                        <IconCaretDown  size={12} />
                    {/snippet}
            </Button>
            {/snippet}

        {#snippet content()}
                <ActionList  selection="single">
                {#each fileTypes as f (f.value)}
                    <ActionListItem 
                        on:select={() => selectFileType(f.value)}
                        selected={type === f.value}
                    >
                        { f.name }

                        {#snippet end()}
                                        <Text small light >
                                { f.extensions }
                            </Text>
                                    {/snippet}
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
            {/snippet}

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