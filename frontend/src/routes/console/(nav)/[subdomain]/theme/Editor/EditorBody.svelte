<script lang="ts">
	import ConfigUiPreview from './Preview/ConfigUiPreview.svelte';
	import { configModeStore } from '../themeStore';
	import { type CodeMirrorMode } from '../../../../lib/components/CodemirrorEditor/codemirror';
	import { selectedThemeFileStore } from "../themeStore";
	import TextEditor from "./Preview/TextEditor.svelte";
	import AssetImage from "./Preview/AssetImage.svelte";
	import { IconMessage, Link } from "@hyvor/design/components";
	import { IconBoxArrowUpRight, IconEyeSlash } from "@hyvor/icons";
	import { blogStore } from "../../../../lib/stores/blogStore";

    let currentFile = $derived($selectedThemeFileStore!);
    let ext = $derived((currentFile.name.split('.').pop() || '') as CodeMirrorMode);

    const textExtensions = ['scss', 'twig', 'js', 'yaml'];
    const imageExtensions = ['png', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'gif', 'apng', 'avif', 'svg', 'webp'];

</script>

{#if currentFile.folder === null && currentFile.name === 'config.yaml' && $configModeStore === 'ui'}
    <ConfigUiPreview file={currentFile} />
{:else if textExtensions.includes(ext)}
    <TextEditor file={currentFile} {ext} />
{:else if currentFile.folder === 'assets' && imageExtensions.includes(ext)}
    <AssetImage file={currentFile} />
{:else}
    <IconMessage message="No preview available">
        {#snippet icon()}
                                <IconEyeSlash  size={60} />
                            {/snippet}
        <!-- @migration-task: migrate this slot by hand, `message` would shadow a prop on the parent component -->
    <div slot="message">
            No preview available.

            {#if currentFile.folder === 'assets'}
                <div style="margin-top:5px;font-size:14px;">
                    <Link
                        href={`${$blogStore.url}/assets/${currentFile.name}`}
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Open in new tab {#snippet end()}
                                                                <IconBoxArrowUpRight  size={14} />
                                                            {/snippet}
                    </Link>
                </div>
            {/if}
        </div>
    </IconMessage>
{/if}