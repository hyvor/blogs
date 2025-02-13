<script lang="ts">
	import { SplitControl } from '@hyvor/design/components';
	import Object from './Object.svelte';
	import ConfigInput from './ConfigInput.svelte';
	import { createEventDispatcher } from 'svelte';

	interface Props {
		// export let config: object;
		configDef: object;
		parentKeys?: string[];
		value: any;
		key: string;
	}

	let { configDef, parentKeys = [], value, key }: Props = $props();

	let currentDef: Record<string, any> = $derived(configDef[key as keyof typeof configDef] || {});
	let name: string = $derived(currentDef.$name || key);
	let description: string = $derived(currentDef.$description || '');
	let hasChildren: boolean = $derived(typeof value === 'object' && value !== null);
	let parentKeysWithCurrentKey: string[] = $derived([...parentKeys, key]);

	const dispatch = createEventDispatcher<{
		change: {
			parentKeys: string[];
			key: string;
			value: any;
		};
	}>();

	function handleChange(e: CustomEvent<any>) {
		dispatch('change', {
			parentKeys,
			key,
			value: e.detail
		});
	}

	function handleObjectChange(e: CustomEvent<any>) {
		dispatch('change', e.detail);
	}
</script>

<SplitControl label={name}>
	{#snippet caption()}
		<div class="caption">
			{@html description}
		</div>
	{/snippet}

	{#if !hasChildren}
		<ConfigInput {value} configDef={currentDef} on:change={handleChange} />
	{/if}

	{#snippet nested()}
		<div>
			{#if hasChildren}
				<Object
					config={value}
					configDef={currentDef}
					parentKeys={parentKeysWithCurrentKey}
					on:change={handleObjectChange}
				/>
			{/if}
		</div>
	{/snippet}
</SplitControl>

<style lang="scss">
	.caption {
		font-size: 14px;
		color: var(--text-light);
		:global(a) {
			color: var(--link);
			&:hover {
				text-decoration: underline;
			}
		}
	}
</style>
