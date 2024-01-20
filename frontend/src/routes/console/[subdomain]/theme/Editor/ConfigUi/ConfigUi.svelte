<script lang="ts">
	import { createEventDispatcher } from "svelte";
    // @ts-ignore
    import yaml from 'js-yaml';
	import { Callout } from "@hyvor/design/components";
	import { addDefaultDefs } from "./configUi";
	import Object from "./Object.svelte";

    export let config: string;
    export let configDef: string;


    let configYaml : object;
    let configDefYaml : object;
    let error : null | string = null;

    $: {

        try {
            configYaml = yaml.load(config);
        } catch (e: any) {
            error = 'Unable to parse config.yaml: ' + e.message
        }

        try {
            configDefYaml = yaml.load(configDef);
        } catch (e: any) {
            error = 'Unable to parse config.def.yaml: ' + e.message;
        }

        if (!error && (!configYaml || typeof configYaml !== 'object')) {
            error = 'Invalid data type in config.yaml. Object required.';
        }

        configDefYaml = addDefaultDefs(configDefYaml || {});

    }

    const dispatch = createEventDispatcher();
</script>

{#if error}
    <Callout type="danger">
        {error}
    </Callout>
{:else}
    <Object 
        config={configYaml} 
        configDef={configDefYaml}
    />
{/if}


