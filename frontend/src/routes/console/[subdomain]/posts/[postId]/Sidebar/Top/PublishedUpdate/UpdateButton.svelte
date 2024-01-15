<script lang="ts">
	import { Button } from "@hyvor/design/components";
    import { postOriginalStore, postStore, postVariantStore } from "../../../../postStore";
	import UpdateModal from "./UpdateModal.svelte";
	import { hasPublishedChanges } from "./published-changes";

    let hasChanges = false;
    let isUpdating = false;

    $: $postStore, $postOriginalStore, hasChanges = hasPublishedChanges();

</script>

{#if $postVariantStore.status === 'published'}

    <Button 
        disabled={!hasChanges}
        on:click={() => isUpdating = true}
    >
        Update
    </Button>

{/if}

{#if isUpdating}
    <UpdateModal bind:show={isUpdating} />
{/if}