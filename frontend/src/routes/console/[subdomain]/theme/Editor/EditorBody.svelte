<script lang="ts">
	import { type CodeMirrorMode } from './../../../lib/components/CodemirrorEditor/codemirror';
	import { selectedThemeFileStore } from "../themeStore";
	import TextEditor from "./Preview/TextEditor.svelte";

    $: currentFile = $selectedThemeFileStore!;
    $: ext = (currentFile.name.split('.').pop() || '') as CodeMirrorMode;

    const textExtensions = ['scss', 'twig', 'js', 'yaml'];
    const imageExtensions = ['png', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'gif', 'apng', 'avif', 'svg', 'webp'];
</script>


{#if textExtensions.includes(ext)}
    <TextEditor file={currentFile} {ext} />
{:else if currentFile.folder === 'assets' && imageExtensions.includes(ext)}

{:else}
    
{/if}