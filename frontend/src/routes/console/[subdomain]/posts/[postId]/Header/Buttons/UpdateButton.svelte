<script lang="ts">
	import { Button } from "@hyvor/design/components";
    import { postOriginalStore, postStore, postVariantStore } from "../../../postStore";
	import { hasPostChanges } from "../../changed";

    let hasChanges = false;

    $: $postStore, $postOriginalStore, hasChanges = hasPostChanges();

</script>

{#if 
    $postVariantStore.status === 'published' ||
    $postVariantStore.status === 'scheduled'
}

    <Button disabled={!hasChanges}>
        Update
    </Button>

{/if}