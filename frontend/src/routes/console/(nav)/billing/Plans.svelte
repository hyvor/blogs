<script lang="ts">
	import { onMount } from 'svelte';
	import { getConfig } from '../../lib/config';

	let wrap: HTMLDivElement | undefined = $state();

	onMount(() => {
		if (!wrap) {
			return;
		}

		const script = document.createElement('script');
		script.src = getConfig().hyvor.instance + '/js/billing-component-iframe.js?component=blogs';
		wrap.appendChild(script);

		return () => {
			wrap?.removeChild(script);
		};
	});
</script>

<div class="content" bind:this={wrap}></div>
