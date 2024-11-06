<script lang="ts">
	import type { SelectedFile as SelectedImageType } from './image-uploader';
	import { Button, Modal, TabNav, TabNavItem } from '@hyvor/design/components';
	import { IconCardImage, IconCaretLeft, IconCloudUpload } from '@hyvor/icons';
	import TabUpload from './TabUpload.svelte';
	import SelectedFile from './PreviewSelected/SelectedFile.svelte';
	import ExcalidrawIcon from './Excalidraw/ExcalidrawIcon.svelte';
	import Excalidraw from './Excalidraw/Excalidraw.svelte';
	import Unsplash from './Unsplash/Unsplash.svelte';
	import Media from './Media/Media.svelte';
	import { createEventDispatcher } from 'svelte';

	export let type: 'image' | 'audio' | 'all' = 'image';

	let tab = 'upload';

	export let show = true;
	let backImage: null | SelectedImageType = null;
	let selectedFile: null | SelectedImageType = null;

	function handleSelect(e: CustomEvent<SelectedImageType>) {
		selectedFile = e.detail;
		backImage = null;
	}

	function handleBack() {
		backImage = selectedFile;
		selectedFile = null;
	}

	const dispatch = createEventDispatcher<{
		select: SelectedImageType;
		close: undefined;
	}>();

	$: if (!show) {
		dispatch('close');
	}

	function handleFinish(e: CustomEvent<SelectedImageType>) {
		dispatch('select', e.detail);
		show = false;
	}
</script>

<div class="image-uploader">
	<Modal bind:show size="large" closeOnEscape={false} closeOnOutsideClick={false}>
		<div slot="title">
			{#if selectedFile}
				<Button on:click={handleBack} color="input">
					<IconCaretLeft slot="start" va />
					Back
				</Button>
			{:else}
				{#if type === 'all'}
					<TabNav bind:active={tab}>
						<TabNavItem name="upload">
							<IconCloudUpload slot="start" />
							Upload Image
						</TabNavItem>
						<TabNavItem name="upload-audio">
							<IconCloudUpload slot="start" />
							Upload Audio
						</TabNavItem>
					</TabNav>
				{:else}
					<TabNav bind:active={tab}>
						<TabNavItem name="upload">
							<IconCloudUpload slot="start" />
							Upload
						</TabNavItem>
						<TabNavItem name="media">
							<IconCardImage slot="start" />
							Media Library
						</TabNavItem>

						{#if type === 'image'}
							<TabNavItem name="unsplash">
								<svg
									role="img"
									width="1em"
									height="1em"
									fill="currentColor"
									viewBox="0 0 24 24"
									xmlns="http://www.w3.org/2000/svg"
									slot="start"
									><path d="M7.5 6.75V0h9v6.75h-9zm9 3.75H24V24H0V10.5h7.5v6.75h9V10.5z" /></svg
								>
								Unsplash
							</TabNavItem>
							<TabNavItem name="excalidraw">
								<ExcalidrawIcon slot="start" />
								Excalidraw
							</TabNavItem>
						{/if}
					</TabNav>
				{/if}
			{/if}
		</div>

		{#if type === 'all'}
			<div class="body" style:position={selectedFile ? 'relative' : undefined}>
				{#if tab === 'upload'}
					<TabUpload type='image' on:select={handleSelect} />
				{:else if tab === 'upload-audio'}
					<TabUpload type='audio' on:select={handleSelect} />
				{/if}
				{#if selectedFile}
					<SelectedFile file={selectedFile} on:select={handleFinish} />
				{/if}
			</div>
		{:else}
			<div class="body" style:position={selectedFile ? 'relative' : undefined}>
				{#if tab === 'upload'}
					<TabUpload {type} on:select={handleSelect} />
				{:else if tab === 'media'}
					<Media {type} on:select={handleSelect} />
				{:else if tab === 'unsplash'}
					<Unsplash on:select={handleSelect} />
				{:else if tab === 'excalidraw'}
					<Excalidraw on:select={handleSelect} />
				{/if}

				{#if selectedFile}
					<SelectedFile file={selectedFile} on:select={handleFinish} />
				{/if}
			</div>
		{/if}
	</Modal>
</div>

<style lang="scss">
	.image-uploader :global(.wrap) {
		z-index: 1000 !important;
	}

	.image-uploader :global(.inner) {
		height: 100%;
		width: 1100px !important;
		display: flex;
		flex-direction: column;
		:global(> .content) {
			flex: 1;
			padding-top: 0;
			min-height: 0;
			display: flex;
			flex-direction: column;
		}
	}

	.body {
		flex: 1;
		min-height: 0;
	}
</style>
