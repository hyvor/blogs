<script lang="ts">
	import { Button, ButtonGroup } from '@hyvor/design/components';
	import FileUploader from '../../../../lib/components/FileUploader/FileUploader.svelte';
	import type { SelectedFile } from '../../../../lib/components/FileUploader/image-uploader';
	import { createEventDispatcher } from 'svelte';

	interface Props {
		src?: string | null;
		uploadText?: string;
	}

	let { src = null, uploadText = 'Upload' }: Props = $props();

	let isUploading = $state(false);

	const dispatch = createEventDispatcher<{ change: string | null }>();

	function handleSelect(file: SelectedFile) {
		isUploading = false;
		dispatch('change', file.url as string);
	}

	function handleRemove() {
		dispatch('change', null);
	}
</script>

{#if !src}
	<Button size="small" on:click={() => (isUploading = true)}>
		{uploadText}
	</Button>
{:else}
	<div class="img-wrap">
		<img {src} alt="Uploaded" />
	</div>

	<div class="buttons">
		<ButtonGroup>
			<Button on:click={() => (isUploading = true)} size="x-small" variant="fill-light">
				Change
			</Button>
			<Button on:click={handleRemove} size="x-small" color="red" variant="fill-light">
				Remove
			</Button>
		</ButtonGroup>
	</div>
{/if}

{#if isUploading}
	<FileUploader onselect={handleSelect} bind:show={isUploading} />
{/if}

<style>
	img {
		max-width: 250px;
		max-height: 250px;
		border-radius: 5px;
	}
	.buttons {
		margin-top: 5px;
	}
</style>
