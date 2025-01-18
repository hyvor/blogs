<script lang="ts">
    import { run } from 'svelte/legacy';

	import { ColorPicker, Radio, Switch, TextInput, Textarea } from "@hyvor/design/components";
    import { getInputType } from "./configUi";
	import { createEventDispatcher } from "svelte";

    interface Props {
        value: any;
        configDef: Record<string, any>;
    }

    let { value = $bindable(), configDef }: Props = $props();

    let type = $derived(getInputType(configDef));

    const dispatch = createEventDispatcher<{change: any}>();

    function handleChange() {
        dispatch('change', value);
    }

    function handleNumberInput(e: Event) {
        value = Number((e.target as HTMLInputElement).value);
    }

    function handleColorChange(e: CustomEvent<string>) {
        value = e.detail;
    }

    run(() => {
        value, handleChange();
    });

</script>

{#if type === 'none'}
    {value}
{:else if type === 'text'}
    <TextInput 
        maxlength={configDef.$maxlength}
        minlength={configDef.$minlength}
        bind:value={value}
        block
    />
{:else if type === 'textarea'}
    <Textarea 
        maxlength={configDef.$maxlength}
        minlength={configDef.$minlength}
        bind:value={value}
        block
    />
{:else if type === 'number'}
    <TextInput  
        type="number"
        min={configDef.$min}
        max={configDef.$max}
        value={value}
        on:input={handleNumberInput}
        block
    />
{:else if type === 'checkbox'}
    <Switch 
        bind:checked={value}
    />
{:else if type === 'radio'}

    {#each Object.entries(configDef.$options) as [key, label]}
        <Radio
            name={configDef.$name}
            value={key}
            bind:group={value}
        >
            {label}
        </Radio>
    {/each}

{:else if type === 'color'}
    
    <ColorPicker 
        color={value}
        on:input={handleColorChange}
    />

{/if}

