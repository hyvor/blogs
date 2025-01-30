<script lang="ts">
	import { Button, ButtonGroup, FormControl, InputGroup, Modal, Radio, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
    import type { Redirect } from "../../../../lib/types";
	import { createNavigation } from "./navigationActions";
	import { createEventDispatcher } from "svelte";
	import { isValidUrl } from "../../../../lib/helper/is-valid-url";

    interface Props {
        show?: boolean;
    }

    let { show = $bindable(false) }: Props = $props();
    let loading = $state(false);

    const dispatch = createEventDispatcher();

    let name = $state('');
    let url = $state('');
    let type : 'header' | 'footer' = $state('header');

    let nameError : null | string = $state(null);
    let urlError : null | string = $state(null);

    function handleClick() {
        nameError = null;
        urlError = null;

        if (!name) {
            nameError = 'Name is required.';
            return;
        }

        if (!url) {
            urlError = 'URL is required.';
            return;
        }

        loading = true;
        createNavigation(name, url, type)
            .then(res => {
                toast.success('Navigation created successfully');
                dispatch('create', res);
                show = false;
            })
            .catch(err => {
                toast.error(err.message);
            }).finally(() => {
                loading = false;
            });

    }   


    let isButtonDisabled = (
            ($derived(name == '' || url == '')))

</script>

<Modal 
    title={'Add new navigation'}
    loading={loading}
    bind:show={show}
>

    <SplitControl
        label="Name"
    >

        <FormControl>
            <TextInput 
                bind:value={name}
                placeholder="About us"
                block
                state={nameError ? 'error' : undefined}
                autofocus
            />

            {#if nameError}
                <Validation state="error">
                    {nameError}
                </Validation>
            {/if}
        </FormControl>

    </SplitControl>

    <SplitControl
        label="URL"
    >
        <FormControl>
            <TextInput 
                bind:value={url}
                placeholder="/about"
                block
                state={urlError ? 'error' : undefined}
            />

            {#if urlError}
                <Validation state="error">
                    {urlError}
                </Validation>
            {/if}
        </FormControl>
    </SplitControl>

    <SplitControl
        label="Type"
    >
        <InputGroup>
            <Radio
                name="type"
                value="header"
                bind:group={type}
            >
                Header
            </Radio>
            <Radio
                name="type"
                value="footer"
                bind:group={type}
            >
                Footer
            </Radio>
        </InputGroup>
    </SplitControl>

    {#snippet footer()}
    

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
                    Add
                </Button>

            </ButtonGroup>

        
    {/snippet}

</Modal>