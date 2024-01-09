<script lang="ts">
	import { Button, ButtonGroup, FormControl, InputGroup, Modal, Radio, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
    import type { Redirect } from "../../../lib/types";
	import { createRedirect, updateRedirect } from "./redirectActions"
	import { createEventDispatcher } from "svelte";
	import { isValidUrl } from "../../../lib/helper/is-valid-url";

    export let redirect: Redirect | null = null;
    export let show = false;
    let loading = false;

    const isCreating = redirect === null;

    const dispatch = createEventDispatcher();

    let from = redirect ? redirect.path : '';
    let to = redirect ? redirect.to : '';
    let type : 'permanent' | 'temporary' = redirect ? redirect.type : 'permanent';

    let fromError : null | string = null;
    let toError : null | string = null;

    function handleClick() {
        fromError = null;
        toError = null;

        if (!from) {
            fromError = 'From is required.';
            return;
        }

        if (!from.startsWith('/')) {
            fromError = 'From value should be a relative path starting with /';
            return;
        }

        if (!to) {
            toError = 'To is required.';
            return;
        }

        if (!isValidUrl(to)) {
            toError = 'To value should be a valid URL.';
            return;
        }


        if (isCreating) {
            loading = true;
            createRedirect(from, to, type)
                .then(res => {
                    toast.success('Redirect created successfully');
                    dispatch('create', res);
                    show = false;
                })
                .catch(err => {
                    toast.error(err.message);
                }).finally(() => {
                    loading = false;
                });

        }   
        else {
            loading = true;
            updateRedirect(redirect!.id, from, to, type)
                .then(res => {
                    toast.success('Redirect updated.');
                    dispatch('update', res);
                    show = false;
                })
                .catch(err => {
                    toast.error(err.message);
                })
                .finally(() => {
                    loading = false;
                })

        }
    }

    $: isButtonDisabled = !(isCreating ||
            (from !== redirect!.path || 
                to !== redirect!.to || 
                type !== redirect!.type))

</script>

<Modal 
    title={isCreating ? 'Add new redirect' : 'Edit redirect'}
    loading={loading}
    bind:show={show}
>

    <SplitControl
        label="From"
        caption="Which path to match"
    >

        <FormControl>
            <TextInput 
                bind:value={from}
                placeholder="/welcome"
                block
                state={fromError ? 'error' : undefined}
                autofocus
            />

            {#if fromError}
                <Validation state="error">
                    {fromError}
                </Validation>
            {/if}
        </FormControl>

    </SplitControl>

    <SplitControl
        label="To"
        caption="An absolute URL to redirect to"
    >
        <FormControl>
            <TextInput 
                bind:value={to}
                placeholder="https://hyvor.com"
                block
                state={toError ? 'error' : undefined}
            />

            {#if toError}
                <Validation state="error">
                    {toError}
                </Validation>
            {/if}
        </FormControl>
    </SplitControl>

    <SplitControl
        label="Type"
        caption="Permanent redirects are cached by browsers."
    >
        <InputGroup>
            <Radio
                name="type"
                value="permanent"
                bind:group={type}
            >
                Permanent (301)
            </Radio>
            <Radio
                name="type"
                value="temporary"
                bind:group={type}
            >
                Temporary (302)
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
                on:click={handleClick}
                disabled={isButtonDisabled}
            >
                {isCreating ? 'Add' : 'Save'}
            </Button>

        </ButtonGroup>

    </svelte:fragment>

</Modal>