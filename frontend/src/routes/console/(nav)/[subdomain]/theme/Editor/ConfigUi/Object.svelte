<script lang="ts">
	import One from './One.svelte';

	interface Props {
		config: object;
		configDef: object;
		parentKeys?: string[];
	}

	let { config, configDef, parentKeys = [] }: Props = $props();
</script>

{#each Object.entries(config) as [key, value] (parentKeys + '.' + key)}
	<One {configDef} {key} {value} {parentKeys} on:change />
{/each}
