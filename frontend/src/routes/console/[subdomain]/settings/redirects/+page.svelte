<script lang="ts">
	import { Button, ButtonGroup, FormControl, Modal, SplitControl, TextInput } from "@hyvor/design/components";
    import RedirectsModal from './RedirectsModal.svelte';

    let showCreateModal = false;
</script>

{#if showCreateModal}
    <RedirectsModal bind:show={showCreateModal} />
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