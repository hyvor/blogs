<script lang="ts">
	import CodemirrorEditor from "../../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte";
	import type { CodeMirrorMode } from "../../../../lib/components/CodemirrorEditor/codemirror";
	import { updateThemeFileStore } from "../../../../lib/stores/themeStore";
	import type { ThemeFile } from "../../../../lib/types";

    export let file: ThemeFile;
    export let ext: CodeMirrorMode;

    function handleChange(val: string) {
        updateThemeFileStore(file.id, {content: val});
    }

</script>

<div class="text-editor">
    <CodemirrorEditor 
        value={file.content || ''}
        ext={ext}
        on:change={e => handleChange(e.detail)}
    />
</div>

<style>
    .text-editor {
        flex: 1;
        min-height: 0;
    }
    .text-editor :global(.CodeMirror) {
        padding: 10px 0 60px;
        border-radius: 0;
        height: 100%;
    }
</style>