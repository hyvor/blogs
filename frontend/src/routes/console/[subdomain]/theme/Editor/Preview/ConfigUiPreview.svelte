<script lang="ts">
	import { themeFilesStore } from "../../themeStore";
	import ConfigUi from "../ConfigUi/ConfigUi.svelte";

    $: files = $themeFilesStore;

    $: configYaml = files.find(f => f.folder === null && f.name === 'config.yaml')?.content || '';
    $: configDefYaml = files.find(f => f.folder === null && f.name === 'config.def.yaml')?.content || '';

</script>

<div>
    <ConfigUi config={configYaml} configDef={configDefYaml} />
</div>


<style>
    div {
        padding: 15px 25px;
        overflow: auto;
    }
</style>