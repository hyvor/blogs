<script lang="ts">
	import { blogStore } from "../../../../../lib/stores/blogStore";
	import type { ThemeFile } from "../../../../../lib/types";

    interface Props {
        file: ThemeFile;
    }

    let { file }: Props = $props();

    let url = $derived($blogStore.url + "/assets/" + file.name);
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
    img {
        max-width: 100%;
    }
</style>