<script lang="ts">
	import { createEventDispatcher } from 'svelte';
	import { TextInput } from '@hyvor/design/components';
	import { IconX } from '@hyvor/icons';
	import { IconPlus } from '@hyvor/icons';
	import { IconCheck } from '@hyvor/icons';
	import { IconButton, Button } from '@hyvor/design/components';
    
    export let keyword = '';

    let isAdding = !!keyword;
    
    const dispatch = createEventDispatcher<{add: string, close: void}>();

    function handleConfirm() {
        dispatch('add', keyword);
        handleClose();
    }

    function handleClose() {
        dispatch('close');
        isAdding = false;
        keyword = '';
    }

    function handleKeyup(e: KeyboardEvent) {
        if (e.key === 'Enter') {
            handleConfirm();
        }
        if (e.key === 'Escape') {
            handleClose();
        }
    }
</script>

<div class="keyword-adder">

    {#if isAdding}

        <div class="adding">
            <TextInput
                autofocus
                bind:value={keyword}
                size="x-small"
                on:keyup={handleKeyup}
            />
            <div class="confirm-buttons">
                <IconButton 
                    size={16}
                    disabled={keyword.trim() === ''}
                    on:click={handleConfirm}
                    variant="invisible"
                    color="gray"
                >
                    <IconCheck />
                </IconButton>

                <IconButton 
                    size={16}
                    on:click={handleClose}
                    variant="invisible"
                    color="gray"
                >
                    <IconX />
                </IconButton>
            </div>
        </div>

    {:else}
        <Button 
            size="x-small" 
            color="input"
            on:click={() => isAdding = true}
        >
            <IconPlus slot="start" />
            Add
        </Button>

    {/if}
</div>   


<style>
    .keyword-adder {
        margin-bottom: 5px;
        
    }
    .adding {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .confirm-buttons {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
</style>