<script lang="ts">
	import { selectedThemeFileStore } from "../themeStore";
    import EditorBody from "./EditorBody.svelte";
    import EditorTopbar from "./EditorTopbar.svelte";
</script>


{#if $selectedThemeFileStore}
    <div class="editor-wrap">
        <EditorTopbar />
        <EditorBody />
    </div>
{/if}

<style>
    .editor-wrap {
        display: flex;
        flex-direction: column;
        min-height: 0;
        height: 100%;
    }
</style>