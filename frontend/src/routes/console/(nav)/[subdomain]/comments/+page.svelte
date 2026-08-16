<script>
	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';
	import { integrationsStore } from '../../../lib/stores/blogStore';
	import { goto } from '$app/navigation';
	import EmbeddedConsole from '../@components/EmbeddedConsole/EmbeddedConsole.svelte';
	import { onMount } from 'svelte';
	import { getConfig } from '../../../lib/config';

	onMount(() => {
		if (!$integrationsStore.hyvor_talk) {
			goto(consoleUrlWithBlog('/'));
		}
	});
</script>

{#if $integrationsStore.hyvor_post}
	<EmbeddedConsole
		url="{getConfig().hyvor.hyvor_talk_url}/console/{$integrationsStore.hyvor_talk
			?.website_id}/comments?embedded=true&website_id={$integrationsStore.hyvor_talk?.website_id}"
		title="Hyvor Talk Comments Console"
	/>
{/if}
