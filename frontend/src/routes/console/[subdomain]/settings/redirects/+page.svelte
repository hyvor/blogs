<script lang="ts">
	import { Button, ButtonGroup, FormControl, Modal, SplitControl, TextInput } from "@hyvor/design/components";


    let showCreateModal = false;
</script>

{#if showCreateModal}

    <Modal
        title="Create Redirect"
        bind:show={showCreateModal}
    >

        <SplitControl
            label="From"
            caption=""
        >
        <FormControl>
            <TextInput 

                placeholder="/welcome"
                block
                autofocus
            />
        </FormControl>
    </SplitControl>

    <SplitControl
        label="To"
        caption=""
        >
        <FormControl>
            <TextInput 
            
                placeholder="https://hyvor.com"
                block
            
            />
        </FormControl>
    </SplitControl>

    <SplitControl
        label="Type"
    >
    </SplitControl>

    <svelte:fragment slot="footer">

        <ButtonGroup>

            <Button 
                variant="invisible"
                on:click={() => showCreateModal = false}
            >
                Cancel
            </Button>

            <Button
            >
            </Button>

        </ButtonGroup>

    </svelte:fragment>

    </Modal>

{/if}

<div class="settings">
    <div class="button_header">
        <Button
            on:click={() => showCreateModal = true}
        >
            Create Redirect +
        </Button>
    </div>
</div>

<style>
     .settings {
        flex: 1;
        overflow: auto;
        padding: 25px 30px;
    }

    .button_header {
        padding: 15px 30px;
        text-align: right;
        border-bottom: 1px solid var(--border);
    }
</style>