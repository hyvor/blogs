<script>
	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';
	import { integrationsStore } from '../../../lib/stores/blogStore';
	import { goto } from '$app/navigation';
	import EmbeddedConsole from '../@components/EmbeddedConsole/EmbeddedConsole.svelte';
	import { onMount } from 'svelte';
	import { getConfig } from '../../../lib/config';

	onMount(() => {
		if (!$integrationsStore.hyvor_post) {
			goto(consoleUrlWithBlog('/'));
		}
	});
</script>

{#if $integrationsStore.hyvor_post}
	<EmbeddedConsole
		url="{getConfig().hyvor
			.hyvor_post_url}/console?embedded=true&newsletter_id={$integrationsStore.hyvor_post
			.newsletter_id}"
		title="Hyvor Post Newsletter Console"
	/>
{/if}
