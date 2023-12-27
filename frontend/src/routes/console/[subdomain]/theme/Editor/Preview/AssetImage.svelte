<script lang="ts">
	import { blogStore } from "../../../../lib/stores";
	import type { ThemeFile } from "../../../../lib/types";

    export let file: ThemeFile;    

    $: url = $blogStore.url + "/assets/" + file.name;
</script>

<div class="asset-image-preview">
    <img 
        src={url} 
        alt={file.name}
    />
</div>

<style>
    .asset-image-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 30px 50px;
        min-width: 0;
        overflow: hidden;
    }
</style>