<script lang="ts">
	import { onMount } from 'svelte';
	import { getConfig } from '../../../../../../../../../lib/config';
	import { addParentResizeEvent } from './iframeParentResize';

	export let url: string;

	onMount(() => {
		addParentResizeEvent();
	});
</script>

<iframe
	src={getConfig().hyvor.instance + '/api/public/unfold/iframe?url=' + encodeURIComponent(url)}
	title="Embed"
	sandbox="allow-scripts allow-same-origin allow-popups allow-presentation"
	allow="fullscreen;accelerometer;clipboard-write;encrypted-media;gyroscope;picture-in-picture;web-share;"
></iframe>

<style>
	iframe {
		width: 100%;
		border: none;
	}
</style>
