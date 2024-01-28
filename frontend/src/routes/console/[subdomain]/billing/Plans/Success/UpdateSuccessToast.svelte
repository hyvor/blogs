<script lang="ts">
	import { onMount } from "svelte";

    let reload = 10;

    onMount(() => {
        const interval = setInterval(() => {
            reload = Math.max(0, reload - 1);

            if (reload === 0) {
                location.reload();
            }
        }, 1000);

        return () => {
            clearInterval(interval);
        }
    })
</script>


Subscription successfully updated. Reloading in {reload} seconds...
