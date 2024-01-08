<script lang="ts">
	import { Button, ButtonGroup, FormControl, InputGroup, Modal, Radio, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
    import type { Redirect } from "../../../lib/types";
	import { createRedirect } from "./redirectAction";

    export let redirect: Redirect | null = null;
    export let show = false;

    const isCreating = redirect === null;

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

        if (!to) {
            toError = 'To is required.';
            return;
        }

        if (isCreating) {

            show = false;

            const toastId = toast.loading('Creating redirect...');

            createRedirect(from, to, type)
                .then(res => {
                    toast.success('Redirect created.', {id: toastId});
                    //languageStoreAdd(res);
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