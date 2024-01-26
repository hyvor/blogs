<script lang="ts">
	import { Button } from "@hyvor/design/components";
    import { postEditingStatusStore, postLanguageStore, postVariantStore } from "../../../../../postStore";
	import { IconMagic } from "@hyvor/icons";
	import TranslateModal from "./TranslateModal.svelte"
    
    let show = false;
</script>

{#if !$postLanguageStore.is_primary}
    <Button 
        size="small" 
        style="margin-inline-end:8px"
        color="input"
        on:click={() => show = true}
        disabled={$postVariantStore.status === 'published' && !$postEditingStatusStore.isEditingPublished}
    >
        Auto-Translate <IconMagic slot="end" />
    </Button>
{/if}

{#if show}
    <TranslateModal bind:show={show} />
{/if}