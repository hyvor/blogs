<script lang="ts">
	import { Button, Loader } from "@hyvor/design/components";
	import { onMount } from "svelte";
	import { loadThemeFiles, selectedThemeFileIdStore, themeFilesOriginalStore, themeFilesStore } from "../../lib/stores/themeStore";
	import Folder from "./Folder.svelte";
	import Upload from "./Upload.svelte";
	import Download from "./Download.svelte";
	import type { ThemeFile } from "../../lib/types";

    let isLoading = true;

    function getFocusFileId(files: ThemeFile[]) {
        const configYaml = files.find(file => file.folder === null && file.name === 'config.yaml');
        if (configYaml) return configYaml.id;

        const firstRoot = files.find(file => file.folder === null);
        if (firstRoot) return firstRoot.id;

        return null;
    }

    onMount(() => {
        loadThemeFiles()
            .then(res => {
                themeFilesStore.set(res);
                themeFilesOriginalStore.set(res);
                selectedThemeFileIdStore.set(getFocusFileId(res));
                isLoading = false;
            })
    });

</script>


<div class="theme-nav">

    <div class="title">
        <span>Theme</span>
        <Button size="small">
            Change
        </Button>
    </div>

    <div class="folders">

        {#if isLoading}
            <Loader full />
        {:else}

            <Folder name="templates" />
            <Folder name="styles" />
            <Folder name="assets" />
            <Folder name="lang" />
            <Folder name={null} />

        {/if}

    </div>

    <div class="theme-bottom">
        <Upload />
        <Download />
    </div>

</div>

<style>

    .theme-nav {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .title {
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid var(--border);
    }
    .title span {
        margin-right: 5px;
    }

    .folders {
        flex: 1;
        overflow: auto;
        padding: 10px 30px;
    }

    .theme-bottom {
        padding: 20px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

</style>