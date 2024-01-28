<script lang="ts">
	import { Radio, Switch, TextInput, Textarea } from "@hyvor/design/components";
    import { getInputType } from "./configUi";

    export let value: any;
    export let configDef: Record<string, any>;

    $: type = getInputType(configDef);
</script>

{#if type === 'none'}
    {value}
{:else if type === 'text'}
    <TextInput 
        maxlength={configDef.$maxlength}
        minlength={configDef.$minlength}
        value={value}
        block
    />
{:else if type === 'textarea'}
    <Textarea 
        maxlength={configDef.$maxlength}
        minlength={configDef.$minlength}
        value={value}
        block
    />
{:else if type === 'number'}
    <TextInput  
        type="number"
        min={configDef.$min}
        max={configDef.$max}
        value={value}
        block
    />
{:else if type === 'checkbox'}
    <Switch 
        checked={value}
    />
{:else if type === 'radio'}

    {#each Object.entries(configDef.$options) as [key, label]}
        <Radio
            name={configDef.$name}
            value={key}
            group={value}
        >
            {label}
        </Radio>
    {/each}

{:else if type === 'color'}
    
    <TextInput 
        type="color"
        value={value}
        style="width: 50px"
    />

{/if}

