<script lang="ts">
	import { createEventDispatcher } from 'svelte';
	import MediaLibrary from '../../../../(nav)/[subdomain]/tools/media/MediaLibrary.svelte';
	import type { SelectedFile } from '../image-uploader';
	import type { Media } from '../../../types';

	const dispatch = createEventDispatcher<{ select: SelectedFile }>();

	interface Props {
		type?: 'image' | 'audio';
	}

	let { type = 'image' }: Props = $props();

	function handleSelect(e: CustomEvent<Media>) {
		const media = e.detail;
		dispatch('select', {
			type,
			url: media.url,
			from: 'media',
			media
		});
	}
</script>

<MediaLibrary
	filterDefaultType={type === 'audio' ? 'audio' : 'images'}
	filterTypeDisabled={true}
	showUpload={false}
	selecting={true}
	on:select={handleSelect}
/>
