<script lang="ts">
	import { Button, IconMessage, LoadButton, Loader, toast } from '@hyvor/design/components';
	import MediaFilter from './MediaFilter.svelte';
	import { getMedia, type FileType, uploadMedia } from './mediaActions';
	import { IconCloudUpload } from '@hyvor/icons';
	import type { Media } from '../../../lib/types';
	import MediaFile from './MediaFile.svelte';
	import { getConfig } from '../../../lib/config';

	export let showUpload = true;
	export let filterDefaultType = null as null | FileType;
	export let filterTypeDisabled = false;
	export let selecting = false;

	let isLoading = true;
	let isLoadingMore = false;
	let hasMore = false;
	let mediaFiles: Media[] = [];

	let uploadInput: HTMLInputElement;
	let isUploading = false;

	let extensions: string[] = [];
	let search: string | null = null;

	function handleUpload() {
		const files = uploadInput?.files;

		if (!files || !files.length) {
			return toast.error('Please select a file');
		}

		const file = files[0];

		if (!file) {
			return toast.error('Please select a file');
		}

		if (file.size > getConfig().limits.max_upload_size) {
			return toast.error('File size is too large. Max file size is 50MB');
		}

		const toastId = toast.loading('Uploading...');
		isUploading = true;

		uploadMedia(file, file.name)
			.then((media) => {
				toast.success('Uploaded', { id: toastId });
				mediaFiles = [media, ...mediaFiles];
			})
			.catch((err) => toast.error(err.message, { id: toastId }))
			.finally(() => (isUploading = false));
	}

	function handleClickUpload() {
		uploadInput?.click();
	}

	const limit = 50;

	function load(more = false) {
		more ? (isLoadingMore = true) : (isLoading = true);

		getMedia(extensions, search, limit, more ? mediaFiles.length : 0)
			.then((res) => {
				isLoading = false;
				isLoadingMore = false;
				hasMore = res.length === limit;
				mediaFiles = more ? [...mediaFiles, ...res] : res;
			})
			.catch((err) => {
				isLoading = false;
				toast.error(err.message);
			});
	}

	function handleChange(e: CustomEvent<{ extensions: string[]; search: string | null }>) {
		extensions = e.detail.extensions;
		search = e.detail.search;

		load();
	}

	function handleDelete(e: CustomEvent<Media>) {
		mediaFiles = mediaFiles.filter((media) => media.id !== e.detail.id);
	}
</script>

<div class="wrap">
	<div class="topbar">
		<MediaFilter
			on:change={handleChange}
			defaultType={filterDefaultType}
			typeDisabled={filterTypeDisabled}
		/>

		{#if showUpload}
			<input type="file" bind:this={uploadInput} style="display:none" on:change={handleUpload} />
			<Button on:click={handleClickUpload}>
				<IconCloudUpload slot="start" />
				Upload
			</Button>
		{/if}
	</div>

	<div class="media-show">
		{#if isLoading}
			<Loader full />
		{:else if !mediaFiles.length}
			<IconMessage empty message="No Media Found" />
		{:else}
			{#each mediaFiles as media (media.id)}
				<MediaFile {media} {selecting} on:select on:delete={handleDelete} />
			{/each}
		{/if}
	</div>

	<LoadButton text="Load More" loading={isLoadingMore} show={hasMore} on:click={() => load(true)} />
</div>

<style>
	.wrap {
		flex: 1;
		height: 100%;
		display: flex;
		flex-direction: column;
	}
	.topbar {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 30px;
		padding: 0 5px;
	}
	.media-show {
		flex: 1;
		padding: 25px 5px;
		overflow: auto;
		display: flex;
		flex-wrap: wrap;
		gap: 15px 10px;
		align-content: flex-start;
	}
</style>
