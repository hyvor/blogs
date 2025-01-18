<script lang="ts">
	import { Button } from "@hyvor/design/components";
	import SelectThemeModal from "./SelectThemeModal.svelte";
    
    let show = false;
</script>

<Button 
    size="small"
    on:click={() => show = true}
>
    Change
</Button>


{#if show}
    <SelectThemeModal bind:show={show} />
{/if}