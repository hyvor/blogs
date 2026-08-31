<script lang="ts">
	import { Button, IconMessage, LoadButton, Loader, toast } from '@hyvor/design/components';
	import MediaFilter from './MediaFilter.svelte';
	import { getMedia, type FileType, uploadMedia } from './mediaActions';
	import IconCloudUpload from '@hyvor/icons/IconCloudUpload';
	import type { Media } from '../../../../lib/types';
	import MediaFile from './MediaFile.svelte';
	import { getConfig } from '../../../../lib/config';
	import type { SelectedFile } from '../../../../lib/components/FileUploader/image-uploader';
	import FileUploader from '../../../../lib/components/FileUploader/FileUploader.svelte';
	import { mount, unmount } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		showUpload?: boolean;
		filterDefaultType?: any;
		filterTypeDisabled?: boolean;
		selecting?: boolean;
	}

	let {
		showUpload = true,
		filterDefaultType = null as null | FileType,
		filterTypeDisabled = false,
		selecting = false
	}: Props = $props();

	let isLoading = $state(true);
	let isLoadingMore = $state(false);
	let hasMore = $state(false);
	let mediaFiles: Media[] = $state([]);

	let uploadInput: HTMLInputElement | undefined = $state();
	let isUploading = false;

	let extensions: string[] = [];
	let search: string | null = null;

	function handleUpload() {
		const files = uploadInput?.files;

		if (!files || !files.length) {
			return toast.error(i18n.t('console.theme.selectFile'));
		}

		const file = files[0];

		if (!file) {
			return toast.error(i18n.t('console.theme.selectFile'));
		}

		if (file.size > getConfig().limits.max_upload_size) {
			return toast.error(i18n.t('console.tools.media.fileTooLarge'));
		}

		const toastId = toast.loading(i18n.t('console.tools.media.uploading'));
		isUploading = true;

		uploadMedia(file, file.name)
			.then((media) => {
				toast.success(i18n.t('console.tools.media.uploaded'), { id: toastId });
				mediaFiles = [media, ...mediaFiles];
			})
			.catch((err) => toast.error(err.message, { id: toastId }))
			.finally(() => (isUploading = false));
	}

	function handleClickUpload() {
		const div = document.createElement('div');
		document.body.appendChild(div);

		const selector = mount(FileUploader, {
			target: div,
			props: {
				type: 'any',
				onselect: () => {
					destroy();
					load();
				},
				onclose: () => {
					destroy();
				}
			}
		});

		function destroy() {
			unmount(selector);
			div.remove();
		}
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

	function handleUpdate(e: CustomEvent<Media>) {
		mediaFiles = mediaFiles.map((media) => (media.id === e.detail.id ? e.detail : media));
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
			<input type="file" bind:this={uploadInput} style="display:none" onchange={handleUpload} />
			<Button on:click={handleClickUpload}>
				{#snippet start()}
					<IconCloudUpload />
				{/snippet}
				{i18n.t('console.theme.upload')}
			</Button>
		{/if}
	</div>

	<div class="media-show">
		{#if isLoading}
			<Loader full />
		{:else if !mediaFiles.length}
			<IconMessage empty message={i18n.t('console.tools.media.noMedia')} />
		{:else}
			{#each mediaFiles as media (media.id)}
				<MediaFile
					{media}
					{selecting}
					on:select
					on:delete={handleDelete}
					on:update={handleUpdate}
				/>
			{/each}
		{/if}
	</div>

	<LoadButton
		text={i18n.t('console.common.loadMore')}
		loading={isLoadingMore}
		show={hasMore}
		on:click={() => load(true)}
	/>
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
