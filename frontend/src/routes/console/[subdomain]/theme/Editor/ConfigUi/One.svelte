<script lang="ts">
	import { SplitControl } from "@hyvor/design/components";
	import Object from "./Object.svelte";
	import ConfigInput from "./ConfigInput.svelte";


    export let config: object;
    export let configDef: object;
    export let parentKeys : string[] = [];
    export let value: any;
    export let key: string;

    let currentDef: Record<string, any>;
    let name: string;
    let description: string;
    let hasChildren: boolean;
    let parentKeysWithCurrentKey: string[];

    $: {
        currentDef = configDef[key as keyof typeof configDef] || {};
        name = currentDef.$name || key;
        description = currentDef.$description || '';
        hasChildren = typeof value === 'object' && value !== null;
        parentKeysWithCurrentKey = [...parentKeys, key];
    }

</script>

<SplitControl
    label={name}
>

    <div slot="caption">
        {@html description}
    </div>

    {#if !hasChildren}
        <ConfigInput 
            value={value} 
            configDef={currentDef}
        />
    {/if}

    <div slot="nested">
        {#if hasChildren}
            <Object 
                config={value} 
                configDef={currentDef}
                parentKeys={parentKeysWithCurrentKey}
            />
        {/if}
    </div>
    
</SplitControl>

<style lang="scss">
    div[slot="caption"] {
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