<script lang="ts">
	import { Button, ButtonGroup, FormControl, Modal, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
	import { createTag } from "./tagActions";
	import { createEventDispatcher } from "svelte";

    export let show: boolean;

    let name: string = '';

    const dispatch = createEventDispatcher();

    function handleClick() {

        const toastId = toast.loading('Creating tag...');

        show = false;

        createTag(name)
            .then(res => {
                toast.success('Tag created.', {id: toastId});
                dispatch('create', res);
            })
            .catch(err => {
                toast.error(err.message, {id: toastId});
            });

    }

    $: isButtonDisabled = name.trim().length === 0;

</script>

<Modal
    title="Create Tag"
    bind:show={show}
>

    <SplitControl
        label="Name"
        caption="The name of the tag"
    >

        <FormControl>
            <TextInput 
                bind:value={name}
                placeholder="Blogging"
                block
                autofocus
            />
        </FormControl>


    </SplitControl>

    <svelte:fragment slot="footer">

        <ButtonGroup>

            <Button
                variant="invisible"
                on:click={() => show = false}
            >
                Cancel
            </Button>

            <Button
                on:click={handleClick}
                disabled={isButtonDisabled}
            >
                Create
            </Button>

        </ButtonGroup>

    </svelte:fragment>

</Modal>