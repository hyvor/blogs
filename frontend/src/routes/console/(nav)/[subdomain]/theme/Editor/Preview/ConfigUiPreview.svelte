<script lang="ts">
	import { Switch } from '@hyvor/design/components';
	import type { ThemeFile } from '../../../../../lib/types';
	import { themeFilesStore, updateThemeFileStore } from '../../themeStore';
	import ConfigUi from '../ConfigUi/ConfigUi.svelte';
	import TextEditor from './TextEditor.svelte';

	interface Props {
		file: ThemeFile;
	}

	let { file }: Props = $props();

	let files = $derived($themeFilesStore);

	let configYaml = $derived(
		files.find((f) => f.folder === null && f.name === 'config.yaml')?.content || ''
	);
	let configDefYaml = $derived(
		files.find((f) => f.folder === null && f.name === 'config.def.yaml')?.content || ''
	);

	function handleChange(e: CustomEvent<string>) {
		updateThemeFileStore(file.id, { content: e.detail }, false);
	}

	let showYaml = $state(false);
</script>

<div class="switch-wrap">
	<Switch bind:checked={showYaml}>Show YAML</Switch>
</div>

{#if showYaml}
	<TextEditor {file} ext="yaml" />
{:else}
	<div class="ui-wrap">
		<ConfigUi config={configYaml} configDef={configDefYaml} on:change={handleChange} />
	</div>
{/if}

<style>
	.ui-wrap {
		padding: 15px 25px;
		overflow: auto;
		position: relative;
	}
	.switch-wrap {
		text-align: center;
		border-bottom: 1px solid var(--border);
		padding: 10px 25px;
	}
</style>
