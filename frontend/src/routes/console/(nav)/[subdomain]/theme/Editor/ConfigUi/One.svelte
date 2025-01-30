<script lang="ts">
    import { run } from 'svelte/legacy';

	import { SplitControl } from "@hyvor/design/components";
	import Object from "./Object.svelte";
	import ConfigInput from "./ConfigInput.svelte";
	import { createEventDispatcher } from "svelte";

    
    interface Props {
        // export let config: object;
        configDef: object;
        parentKeys?: string[];
        value: any;
        key: string;
    }

    let {
        configDef,
        parentKeys = [],
        value,
        key
    }: Props = $props();

    let currentDef: Record<string, any>= $state({});
    let name: string = $state('');
    let description: string = $state('');
    let hasChildren: boolean = $state(false);
    let parentKeysWithCurrentKey: string[] = $state([]);

    run(() => {
        currentDef = configDef[key as keyof typeof configDef] || {};
        name = currentDef.$name || key;
        description = currentDef.$description || '';
        hasChildren = typeof value === 'object' && value !== null;
        parentKeysWithCurrentKey = [...parentKeys, key];
    });

    const dispatch = createEventDispatcher<{change: {
        parentKeys: string[],
        key: string,
        value: any
    }}>();

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

<SplitControl
    label={name}
>

    {#snippet caption()}
        <div class="caption">
            {@html description}
        </div>
    {/snippet}

    {#if !hasChildren}
        <ConfigInput 
            value={value} 
            configDef={currentDef}
            on:change={handleChange}
        />
    {/if}

    {#snippet nested()}
        <div >
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