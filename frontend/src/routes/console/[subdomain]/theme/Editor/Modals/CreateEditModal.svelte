<script lang="ts">
	import { Button, ButtonGroup, Caption, FormControl, Loader, Modal, SplitControl, Text, TextInput, Validation, toast } from "@hyvor/design/components";
	import { checkFilename, createFile, updateFile } from "../../themeActions";
	import type { ThemeFile, ThemeFolder } from "../../../../lib/types";
	import { onMount } from "svelte";
	import { addThemeFileToStore, selectedThemeFileIdStore, updateThemeFileStore } from "../../themeStore";

    export let open = false;
    export let file: {id: number | null, name: string, folder: ThemeFolder};

    $: isCreate = file.id === null;

    let fileName = '';

    let loaderState : 'none' | 'loading' | 'success' | 'error' = 'none';
    let inputState : 'default' | 'success' | 'error' = 'default'
    let error : string | null = null;

    function handleUpdate() {
        const toastId = toast.loading('Updating file name...');
        open = false;

        updateFile(file.id!, {
            name: fileName
        }).then(() => {
            toast.success('File name updated', {id: toastId});
            updateThemeFileStore(file.id!, {name: fileName}, true);
        }).catch(() => {
            toast.error('Failed to update file name', {id: toastId});
        })
    }

    function handleCreate() {
        const toastId = toast.loading('Creating file...');
        open = false;

        createFile(file.folder, fileName)
            .then(res => {
                toast.success('File created', {id: toastId});
                addThemeFileToStore(res);
                selectedThemeFileIdStore.set(res.id);
            })
            .catch(() => {
                toast.error('Failed to create file', {id: toastId});
            })
    }

    let timeout : null | ReturnType<typeof setTimeout> = null;
    let abortController : AbortController | null = null;

    function validateFolder(name: string) : {state: boolean, error: string|null} {

        if (file.folder === 'lang' && !name.endsWith('.yaml')) {
            return {
                state: false,
                error: 'File name in the lang folder must end with .yaml'
            }
        }

        if (file.folder === 'styles' && !name.endsWith('.scss')) {
            return {
                state: false,
                error: 'File name in the styles folder must end with .scss'
            };
        }

        if (file.folder === 'templates' && !name.endsWith('.twig')) {
            return {
                state: false,
                error: 'File name in the templates folder must end with .twig'
            };
        }

        return {
            state: true,
            error: null
        };

    }

    function handleInput(e: any) {
        const value = e.target.value;

        if (timeout) {
            clearTimeout(timeout);
        }
        if (abortController) {
            abortController.abort();
        }

        if (value.trim() === '') {
            loaderState = 'none';
            inputState = 'error';
            error = 'File name cannot be empty'
            return;
        }
        
        if (value.trim() === file.name) {
            loaderState = 'none';
            inputState = 'default';
            error = null;
            return;
        }

        const {state, error: err} = validateFolder(value);

        if (!state) {
            loaderState = 'none';
            inputState = 'error';
            error = err;
            return;
        }

        timeout = setTimeout(() => {
            loaderState = 'loading';
            inputState = 'default';
            error = null;

            abortController = new AbortController();

            checkFilename(value, file.folder, abortController.signal)
                .then(res => {
                    if (res.available) {
                        loaderState = 'success';
                        inputState = 'success';
                    } else {
                        loaderState = 'error';
                        inputState = 'error';
                        error = 'File name already exists';
                    }

                })
                .catch(e => {

                    if (abortController?.signal.aborted) {
                        return;
                    }

                    loaderState = 'error';
                    inputState = 'error';
                    error = e.error;
                })
        }, 250);

    }

    onMount(() => {
        fileName = file.name;
    })

</script>


<Modal bind:show={open} title={isCreate ? "Create new file" : "Update file name"}>

    <SplitControl
        label="Folder"
    >
        <Text light>/{file.folder || ''}</Text>
    </SplitControl>

    <SplitControl
        label="File Name"
    >

        <FormControl>

            <TextInput 
                block 
                autofocus
                on:input={handleInput}
                bind:value={fileName}
                state={inputState}
            >
                <Loader state={loaderState} slot="end" size="small" duration={10000} />
            </TextInput>
            {#if error}
                <Validation state="error">{error}</Validation>
            {/if}

        </FormControl>

    </SplitControl>

    <svelte:fragment slot="footer">

        <ButtonGroup>

            <Button color="invisible" on:click={() => open = false}>
                Cancel
            </Button>

            <Button 
                color="accent" 
                on:click={() => isCreate ? handleCreate() : handleUpdate()}
                disabled={inputState !== 'success'}
            >
                {isCreate ? 'Create' : 'Update'}
            </Button>

        </ButtonGroup>

    </svelte:fragment>

</Modal>