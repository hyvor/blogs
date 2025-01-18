<script lang="ts">
    import { run } from 'svelte/legacy';

	import { createEventDispatcher } from "svelte";
    // @ts-ignore
    import yaml from 'js-yaml';
    import deepmerge from 'deepmerge';
	import { Callout } from "@hyvor/design/components";
	import { addDefaultDefs } from "./configUi";
	import Object from "./Object.svelte";

    interface Props {
        config: string;
        configDef: string;
    }

    let { config, configDef }: Props = $props();

    let configYaml : object = $state();
    let configDefYaml : object = $state();
    let error : null | string = $state(null);

    run(() => {

        error = null;

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

    });

    const dispatch = createEventDispatcher<{change: string}>();

    function handleChange(e: CustomEvent<{
        parentKeys: string[],
        key: string,
        value: any
    }>) {

        function createUpdatingObject(parentKeys: string[], key: string, value: any) {
            let updatingObject : any = {};

            if (parentKeys.length === 0) {
                updatingObject[key] = value;
            } else {
                updatingObject = {
                    [parentKeys[0]!]: createUpdatingObject(parentKeys.slice(1), key, value)
                }
            }
            return updatingObject;
        }

        function getNewConfig(configState: object, parentKeys: string[], key: string, value: any) : object {
            const updatingObject = createUpdatingObject(parentKeys, key, value);
            return deepmerge(configState, updatingObject);
        }

        const newConfig = getNewConfig(
            configYaml, 
            e.detail.parentKeys, 
            e.detail.key, 
            e.detail.value
        );

        configYaml = newConfig;
        dispatch('change', yaml.dump(newConfig));

    }

</script>

{#if error}
    <Callout type="danger">
        {error}
    </Callout>
{:else}
    <Object 
        config={configYaml} 
        configDef={configDefYaml}
        on:change={handleChange}
    />
{/if}


