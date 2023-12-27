<script lang="ts">
	import { selectedThemeFileStore } from "../../../lib/stores/themeStore";
import EditorBody from "./EditorBody.svelte";
    import EditorTopbar from "./EditorTopbar.svelte";
</script>


{#if $selectedThemeFileStore}
    <div class="editor-wrap">
        <EditorTopbar />
        <EditorBody />
    </div>
{/if}