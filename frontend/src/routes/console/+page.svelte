<script lang="ts">
	import { goto } from "$app/navigation";
	import { onMount } from "svelte";
	import { blogListStore } from "./lib/stores";

    onMount(() => {
        if ($blogListStore.length > 0) {
            goto('/console/' + $blogListStore[0]!.subdomain, {replaceState: true})
        } else {
            goto('/console/new', {replaceState: true})
        }
    });

</script>