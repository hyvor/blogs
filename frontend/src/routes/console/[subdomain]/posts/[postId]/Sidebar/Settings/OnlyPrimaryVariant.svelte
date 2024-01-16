<script lang="ts">
	import { postLanguageStore } from "../../../postStore";
</script>
<div
    class:disabled={!$postLanguageStore.is_primary}
>
    <slot />
</div>

<style>
    div.disabled {
        opacity: 0.3;
        pointer-events: none;
    }
</style>