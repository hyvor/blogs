<script lang="ts">
	import { goto } from '$app/navigation';
	import { onMount } from 'svelte';
	import { blogListStore } from './lib/stores';
	import { consoleUrl } from './lib/consoleUrl';

	onMount(() => {
		if ($blogListStore.length > 0) {
			goto(consoleUrl($blogListStore[0]!.subdomain), { replaceState: true });
		} else {
			goto(consoleUrl('new'), { replaceState: true });
		}
	});
</script>
