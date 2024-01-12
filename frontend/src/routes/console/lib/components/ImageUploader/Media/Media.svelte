<script lang="ts">
	import { createEventDispatcher } from "svelte";
	import MediaLibrary from "../../../../[subdomain]/tools/media/MediaLibrary.svelte";
	import type { SelectedImage } from "../image-uploader";
	import type { Media } from "../../../types";

	const dispatch = createEventDispatcher<{select: SelectedImage}>();

	function handleSelect(e: CustomEvent<Media>) {
		const media = e.detail;
		dispatch('select', {
			url: media.url,
			from: 'media',
			media
		});
	}

</script>

<MediaLibrary 
	filterDefaultType="images"
	filterTypeDisabled={true}
	showUpload={false}
	on:select={handleSelect}
/>