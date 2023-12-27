<script lang="ts">
	import { selectedThemeFileStore } from "../../../lib/stores/themeStore";

    $: currentFile = $selectedThemeFileStore!;
    $: ext = currentFile.name.split('.').pop() || '';

    const textExtensions = ['scss', 'twig', 'js', 'yaml'];
    const imageExtensions = ['png', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'gif', 'apng', 'avif', 'svg', 'webp'];
</script>

<div class="file-preview">

    {#if textExtensions.includes(ext)}


    {:else if currentFile.folder === 'assets' && imageExtensions.includes(ext)}

        

    {:else}



    {/if}

</div>