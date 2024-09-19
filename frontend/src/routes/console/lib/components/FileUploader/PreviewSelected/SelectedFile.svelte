<script lang="ts">
	// import Cropper from 'cropperjs';
	// import 'cropperjs/dist/cropper.css';
	import Meta from './Meta.svelte';
	import type { SelectedFile } from '../image-uploader';
	import { Button, Loader, Switch, toast } from '@hyvor/design/components';
	import { IconCheckAll, IconCloudUpload } from '@hyvor/icons';
	import byteFormatter from '../../../helper/byte-formatter';
	import { createEventDispatcher, onDestroy, onMount } from 'svelte';
	import { uploadMedia } from '../../../../[subdomain]/tools/media/mediaActions';

	export let file: SelectedFile;

	const fileUrl = file.url instanceof Blob ? URL.createObjectURL(file.url) : file.url;
	let imageSize = file.url instanceof File ? file.url.size : null;
	const imageName =
		file.url instanceof File ? file.url.name : file.media ? file.media.original_name : null;

	let imgEl: HTMLImageElement;

	let width = 0;
	let height = 0;

	function getShouldUpload() {
		if (file.from === 'excalidraw' || file.from == 'upload') return true;
		return false;
	}

	function getCanChangeUpload() {
		return file.from === 'upload' && file.upload?.type === 'url' && file.upload?.originalUrl;
	}

	function getHosting() {
		if (file.from === 'upload') {
			if (file.upload?.type === 'url' && file.upload?.originalUrl) {
				const originalUrl = file.upload.originalUrl!;
				const domain = new URL(originalUrl).hostname;
				return `External (${domain})`;
			}
		}

		if (file.from === 'unsplash') {
			return 'Unsplash';
		}

		if (file.from === 'media') {
			return 'Media Library';
		}

		return null;
	}

	let shouldUpload = getShouldUpload();
	const canChangeUpload = getCanChangeUpload();
	const hosting = getHosting();

	function handleImageLoad() {
		width = imgEl.naturalWidth;
		height = imgEl.naturalHeight;
	}

	function tryGetSize() {
		fetch(fileUrl)
			.then((res) => res.blob())
			.then((blob) => {
				imageSize = blob.size;
			})
			.catch((err) => {
				console.error(err);
			});
	}

	const dispatch = createEventDispatcher<{ select: SelectedFile }>();

	function handleSelect(img: SelectedFile = file) {
		dispatch('select', img);
	}

	let isUploading = false;

	function handleUpload() {
		if (shouldUpload && file.url instanceof Blob) {
			isUploading = true;
			uploadMedia(file.url, imageName)
				.then((res) => {
					handleSelect({
						...file,
						url: res.url
					});
				})
				.catch((err) => {
					toast.error(err.message || 'Failed to upload image');
				})
				.finally(() => {
					isUploading = false;
				});
		} else {
			handleSelect({
				...file,
				url: file.upload?.originalUrl || file.url
			});
		}
	}

	onMount(() => {
		if (imageSize === null) {
			tryGetSize();
		}
	});

	onDestroy(() => {
		URL.revokeObjectURL(fileUrl);
	});
</script>

<div class="selected-image">
	{#if isUploading}
		<Loader full>Uploading...</Loader>
	{:else}
		<div class="img-wrap">
			{#if file.type === 'audio'}
				<audio src={fileUrl} controls />
			{:else}
				<img src={fileUrl} alt="Editing" bind:this={imgEl} on:load={handleImageLoad} />
			{/if}
		</div>

		<div class="top-bar">
			<div class="meta">
				{#if file.type === 'image'}
					<Meta name="Dimensions (px)">
						{width} x {height}
					</Meta>
				{/if}
				<Meta name="File Size">
					{#if imageSize !== null}
						{byteFormatter(imageSize)}
					{:else}
						Unknown
					{/if}
				</Meta>
				{#if imageName}
					<Meta name="Name">
						{imageName}
					</Meta>
				{/if}
				{#if hosting}
					<Meta name="Hosting">
						{hosting}
					</Meta>
				{/if}
			</div>
			<div class="upload-switch">
				Upload to Media Library
				<Switch bind:checked={shouldUpload} disabled={!canChangeUpload} />
			</div>
		</div>

		<div class="footer">
			<Button on:click={handleUpload}>
				{shouldUpload ? 'Upload' : 'Select'}
				<svelte:fragment slot="end">
					{#if shouldUpload}
						<IconCloudUpload />
					{:else}
						<IconCheckAll />
					{/if}
				</svelte:fragment>
			</Button>
		</div>
	{/if}
</div>

<style>
	.selected-image {
		position: absolute;
		z-index: 1000000;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: var(--box-background);
		display: flex;
		flex-direction: column;
	}

	.top-bar {
		padding: 10px 25px;
		border-top: 1px solid var(--border);
		border-bottom: 1px solid var(--border);
		margin: 0 -25px;
		display: flex;
		align-items: center;
	}

	.meta {
		display: flex;
		flex: 1;
	}

	.img-wrap {
		display: flex;
		align-items: center;
		justify-content: center;
		height: 100%;
		padding: 25px;
		flex: 1;
		min-height: 0;
		min-width: 0;
		margin-bottom: 20px;
	}
	.img-wrap :global(img) {
		display: block;
		max-width: 100%;
	}
	img {
		max-width: 100%;
		max-height: 100%;
	}

	.upload-switch {
		display: inline-flex;
		align-items: center;
		gap: 10px;
		font-size: 14px;
	}

	.footer {
		padding: 5px 25px;
		padding-top: 15px;
		text-align: center;
	}
</style>
