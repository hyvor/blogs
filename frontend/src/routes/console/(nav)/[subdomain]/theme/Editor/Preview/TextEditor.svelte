<script lang="ts">
	import CodemirrorEditor from "../../../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte";
	import type { CodeMirrorMode } from "../../../../../lib/components/CodemirrorEditor/codemirror";
	import { updateThemeFileStore } from "../../themeStore";
	import type { ThemeFile } from "../../../../../lib/types";
	import { saveCurrentFile } from "../../theme";

    export let file: ThemeFile;
    export let ext: CodeMirrorMode;

    function handleChange(val: string) {
        updateThemeFileStore(file.id, {content: val});
    }

    function handleTextSave(e: CustomEvent<string>) {
        saveCurrentFile();
    }

    function handleKeydown(e: KeyboardEvent) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
        }
    }

</script>

<svelte:window on:keydown={handleKeydown} />

<div class="text-editor">
    <CodemirrorEditor 
        value={file.content || ''}
        id={file.id}
        ext={ext}
        on:change={e => handleChange(e.detail)}
        on:save={handleTextSave}
    />
</div>

<style>
    .text-editor {
        flex: 1;
        min-height: 0;
        height: 100%;
    }
    .text-editor :global(.CodeMirror) {
        padding: 10px 0 60px;
        border-radius: 0;
        height: 100%;
    }
</style>