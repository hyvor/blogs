<script lang="ts">
	import { Button, ButtonGroup, InputGroup, Modal, Radio, SplitControl, TextInput, toast } from "@hyvor/design/components";
	import { createApiKey } from "./apiKeysActions";
	import { createEventDispatcher } from "svelte";
    
    interface Props {
        show: boolean;
    }

    let { show = $bindable() }: Props = $props();

    let api: 'console' | 'delivery' = $state('console');
    let name = $state('');

    let isButtonDisabled = $derived(name.trim().length === 0);

    const dispatch = createEventDispatcher();

    function handleClick() {

        show = false;

        const toastId = toast.loading('Creating API Key...');

        createApiKey(name, api)
            .then(res => {
                toast.success('API Key created successfully', {id: toastId});
                dispatch('create', res);
            })
            .catch(err => {
                toast.error(err.message, {id: toastId});
            });

    }

</script>


<Modal
    title="Create API Key"
    bind:show
>

    <SplitControl 
        label="API"
    >

        <InputGroup>
            
            <Radio 
                value="console"
                bind:group={api}
            >
                Console API
            </Radio>

            <Radio 
                value="delivery"
                bind:group={api}
            >
                Delivery API
            </Radio>
            
        </InputGroup>

    </SplitControl>

    <SplitControl
        label="Name"
        caption="Just for your reference"
    >

        <TextInput 
            bind:value={name}
            block
            placeholder="My API Key"
            autofocus
            maxlength="50"
        />

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
                    Create
                </Button>

            </ButtonGroup>

        
    {/snippet}

</Modal>