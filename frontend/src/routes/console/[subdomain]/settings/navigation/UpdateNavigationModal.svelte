<script lang="ts">
	import { Button, ButtonGroup, FormControl, InputGroup, Link, Modal, Radio, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
	import type { Navigation, NavigationVariant } from "../../../lib/types";
	import VariantInput from "../@components/VariantInput/VariantInput.svelte";
	import { updateNagivation, updateNavigationVariant } from "./navigationActions";
	import { createEventDispatcher } from "svelte";

    export let show = false;
    export let navigation: Navigation;

    let url = navigation.url;
    let type = navigation.type;

    let urlError : null | string = null;

    const variantChanges : Record<number, Partial<NavigationVariant>> = {};

    function handleNameChange(e: CustomEvent<{languageId: number, value: string}>) {
        variantChanges[e.detail.languageId] = {
            name: e.detail.value,
        }
    }

    $: hasChanges = Object.keys(variantChanges).length !== 0 ||
        url !== navigation.url || type !== navigation.type;

    const dispatch = createEventDispatcher();

    let isUpdating = false;

    async function handleUpdate() {

        if (!url) {
            urlError = 'URL is required.';
            return;
        }

        isUpdating = true;

        const toastId = toast.loading('Updating navigation');
        for (const [languageId, changes] of Object.entries(variantChanges)) {
            try {
                await updateNavigationVariant(navigation.id, Number(languageId), changes, type, url);
            } catch (e) {
                toast.error('Failed to update navigation variant', {id: toastId});
                isUpdating = false;
                return;
            }
        }

        const updates : Partial<Navigation> = {};

        if (url !== navigation.url) {
            updates.url = url;
        }
        
        if (type !== navigation.type) {
            updates.type = type;
        }

        updateNagivation(navigation.id, updates)
            .then(res => {
                toast.success('Navigation updated', {id: toastId});
                show = false;
                dispatch('update', res);
            })
            .catch(e => {
                toast.error('Failed to update navigation', {id: toastId});
            })
            .finally(() => {
                isUpdating = false;
            });
        
    }

</script>

<Modal 
    title="Edit navigation"
    bind:show={show}
>

    <VariantInput
        obj={navigation}
        type="navigation"
        key="name"
        label="Name"
        caption="Name of the navigation"
        on:variantCreate
        on:change={handleNameChange}
    />

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

    <svelte:fragment slot="footer">

        <ButtonGroup>

            <Button
                variant="invisible"
                on:click={() => show = false}
            >
                Cancel
            </Button>

            <Button
                on:click={handleUpdate}
                disabled={!hasChanges || isUpdating}
            >
                Update
            </Button>

        </ButtonGroup>
        
    </svelte:fragment>

</Modal>