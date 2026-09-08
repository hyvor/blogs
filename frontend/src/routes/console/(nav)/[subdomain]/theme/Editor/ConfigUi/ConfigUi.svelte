<script lang="ts">
	import { createEventDispatcher } from 'svelte';
	import { load as yamlLoad, dump as yamlDump } from 'js-yaml';
	import deepmerge from 'deepmerge';
	import { Callout } from '@hyvor/design/components';
	import { addDefaultDefs } from './configUi';
	import Object from './Object.svelte';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		config: string;
		configDef: string;
	}

	let { config, configDef }: Props = $props();

	let { configYaml, configDefYaml, error } = $derived.by(() => {
		let configYaml: object = {};
		let configDefYaml: object = {};
		let error: null | string = null;

		try {
			configYaml = yamlLoad(config) as object;
		} catch (e: any) {
			error = i18n.t('console.theme.config.parseConfigError', { message: e.message });
		}

		try {
			configDefYaml = yamlLoad(configDef) as object;
		} catch (e: any) {
			error = i18n.t('console.theme.config.parseConfigDefError', { message: e.message });
		}

		if (!error && (!configYaml || typeof configYaml !== 'object')) {
			error = i18n.t('console.theme.config.invalidType');
		}

		configDefYaml = addDefaultDefs(configDefYaml || {});

		return { configYaml, configDefYaml, error };
	});

	const dispatch = createEventDispatcher<{ change: string }>();

	function handleChange(
		e: CustomEvent<{
			parentKeys: string[];
			key: string;
			value: any;
		}>
	) {
		function createUpdatingObject(parentKeys: string[], key: string, value: any) {
			let updatingObject: any = {};

			if (parentKeys.length === 0) {
				updatingObject[key] = value;
			} else {
				updatingObject = {
					[parentKeys[0]!]: createUpdatingObject(parentKeys.slice(1), key, value)
				};
			}
			return updatingObject;
		}

		function getNewConfig(
			configState: object,
			parentKeys: string[],
			key: string,
			value: any
		): object {
			const updatingObject = createUpdatingObject(parentKeys, key, value);
			return deepmerge(configState, updatingObject);
		}

		const newConfig = getNewConfig(configYaml, e.detail.parentKeys, e.detail.key, e.detail.value);

		// configYaml = newConfig;
		dispatch('change', yamlDump(newConfig));
	}
</script>

{#if error}
	<Callout type="danger">
		{error}
	</Callout>
{:else}
	<Object config={configYaml} configDef={configDefYaml} on:change={handleChange} />
{/if}
