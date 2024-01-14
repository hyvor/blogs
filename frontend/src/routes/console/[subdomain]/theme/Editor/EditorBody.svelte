<script lang="ts">
	import { type CodeMirrorMode } from './../../../lib/components/CodemirrorEditor/codemirror';
	import { selectedThemeFileStore } from "../themeStore";
	import TextEditor from "./Preview/TextEditor.svelte";
	import AssetImage from "./Preview/AssetImage.svelte";
	import { IconMessage, Link } from "@hyvor/design/components";
	import { IconBoxArrowUpRight, IconEyeSlash } from "@hyvor/icons";
	import { blogStore } from "../../../lib/stores/blogStore";

    $: currentFile = $selectedThemeFileStore!;
    $: ext = (currentFile.name.split('.').pop() || '') as CodeMirrorMode;

    const textExtensions = ['scss', 'twig', 'js', 'yaml'];
    const imageExtensions = ['png', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'gif', 'apng', 'avif', 'svg', 'webp'];
</script>


{#if textExtensions.includes(ext)}
    <TextEditor file={currentFile} {ext} />
{:else if currentFile.folder === 'assets' && imageExtensions.includes(ext)}
    <AssetImage file={currentFile} />
{:else}
    <IconMessage message="No preview available">
        <IconEyeSlash slot="icon" size={60} />
        <div slot="message">
            No preview available.

            {#if currentFile.folder === 'assets'}
                <div style="margin-top:5px;font-size:14px;">
                    <Link
                        href={`${$blogStore.url}/assets/${currentFile.name}`}
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Open in new tab <IconBoxArrowUpRight slot="end" size={14} />
                    </Link>
                </div>
            {/if}
    </IconMessage>
{/if}