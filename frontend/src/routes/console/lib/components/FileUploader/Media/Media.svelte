<script lang="ts">
	import { createEventDispatcher } from "svelte";
	import MediaLibrary from "../../../../[subdomain]/tools/media/MediaLibrary.svelte";
	import type { SelectedImage } from "../image-uploader";
	import type { SelectedAudio } from "../audio-uploader";
	import type { Media } from "../../../types";

	const dispatch = createEventDispatcher<{select: SelectedImage, audioSelect: SelectedAudio}>();

	export let isAudio = false;

	function handleSelect(e: CustomEvent<Media>) {
		const media = e.detail;
		dispatch('select', {
			url: media.url,
			from: 'media',
			media
		});
	}

	function handleAudioSelect(e: CustomEvent<Media>) {
		const media = e.detail;
		dispatch('audioSelect', {
			src: media.url,
			from: 'media',
			media
		});
	}

</script>

<MediaLibrary 
	filterDefaultType={isAudio ? 'audio' : 'images'}
	filterTypeDisabled={true}
	showUpload={false}
	selecting={true}
	on:select={isAudio ? handleAudioSelect : handleSelect}
/>