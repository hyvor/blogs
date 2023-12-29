<script lang="ts">
	import { Button, ButtonGroup, Caption, FormControl, Loader, Modal, TextInput, toast } from "@hyvor/design/components";
	import { updateFile } from "../../../../lib/actions/themeActions";
	import type { ThemeFile } from "../../../../lib/types";
	import { onMount } from "svelte";

    export let open = false;
    export let file: ThemeFile;

    let fileName = '';

    let fileNameState : 'none' | 'loading' | 'success' | 'error' = 'none';

    function handleUpdate() {
        const toastId = toast.loading('Updating file name...');

        updateFile(file.id, {
            name: fileName
        }).then(() => [
            toast.success('File name updated', {id: toastId}),
            open = false
        ]);
    }

    function handleInput() {

        fileNameState = 'loading';

        setTimeout(() => {
            fileNameState = 'success';
        }, 2000)

    }

    onMount(() => {
        fileName = file.name;
    })

</script>


<Modal bind:show={open} title="Update file name">

    <FormControl>
        <Caption>
            Enter new file name
        </Caption>
        <TextInput 
            block 
            autofocus
            on:input={handleInput}
            bind:value={fileName}
        >
            <Loader state={fileNameState} slot="end" size="small" />
        </TextInput>
    </FormControl>

    <svelte:fragment slot="footer">

        <ButtonGroup>

            <Button color="invisible" on:click={() => open = false}>
                Cancel
            </Button>

            <Button 
                color="accent" 
                on:click={handleUpdate}
                disabled={file.name === fileName.trim()}
            >
                Update
            </Button>

        </ButtonGroup>

    </svelte:fragment>

</Modal>