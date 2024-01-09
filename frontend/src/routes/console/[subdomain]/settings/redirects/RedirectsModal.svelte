<script lang="ts">
	import { Button, ButtonGroup, FormControl, InputGroup, Modal, Radio, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
    import type { Redirect } from "../../../lib/types";
	import { createRedirect, updateRedirect } from "./redirectActions"
	import { createEventDispatcher } from "svelte";

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
        loading = true;
        fromError = null;
        toError = null;

        if (!from) {
            fromError = 'From is required.';
            return;
        }

        if (!to) {
            toError = 'To is required.';
            return;
        }

        if (!from.startsWith('/')) {
            fromError = 'From value should be a relative path starting with /';
            return;
        }

        if (isCreating) {
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
            show = false;

            const toastId = toast.loading('Updating redirect...');

            updateRedirect(redirect!.id, from, to, type)
                .then(res => {
                    toast.success('Redirect updated.', {id: toastId});
                    dispatch('update', res);
                    show = false;
                })
                .catch(err => {
                    toast.error(err.message, {id: toastId});
                });

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
        caption=""
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
        caption=""
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
    >
        <InputGroup>
            <Radio
                name="type"
                value="permanent"
                bind:group={type}
            >
                Permanent
            </Radio>
            <Radio
                name="type"
                value="temporary"
                bind:group={type}
            >
                Temporary
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