<script lang="ts">
	import One from "./One.svelte";

    export let config: object;
    export let configDef: object;
    export let parentKeys : string[] = [];
</script>

{#each Object.entries(config) as [key, value] (parentKeys + "." + key)}
    <One
        {configDef} 
        {key} 
        {value} 
        {parentKeys}
        on:change
    />
{/each}