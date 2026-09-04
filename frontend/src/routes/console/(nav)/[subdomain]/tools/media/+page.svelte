<script lang="ts">
	import { onMount } from 'svelte';
	import { redirectIfCant } from '../../../../lib/scope.svelte';
	import MediaLibrary from './MediaLibrary.svelte';

	onMount(() => {
		redirectIfCant('media.manage');
	});
</script>

<div class="media hds-box">
	<MediaLibrary />
</div>

<style>
	.media {
		flex: 1;
		padding: 25px;
	}
</style>
