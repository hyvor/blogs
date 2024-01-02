<script lang="ts">
	import { Button } from "@hyvor/design/components";
    import { postOriginalStore, postStore, postVariantStore } from "../../../postStore";
	import { hasPostChanges } from "../../changed";
	import UpdateModal from "../../Header/Modals/UpdateModal.svelte";

    let hasChanges = false;
    let isUpdating = false;

    $: $postStore, $postOriginalStore, hasChanges = hasPostChanges();

</script>

{#if 
    $postVariantStore.status === 'published' ||
    $postVariantStore.status === 'scheduled'
}

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