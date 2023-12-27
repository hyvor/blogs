<script lang="ts">
	import { selectedThemeFileStore } from "../../../lib/stores/themeStore";


    $: currentFile = $selectedThemeFileStore!;
</script>