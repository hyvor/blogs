<script lang="ts">
	import { Button, IconMessage, LoadButton, Loader, toast } from '@hyvor/design/components';
	import MediaFilter from './MediaFilter.svelte';
	import { getMedia, type FileType } from './mediaActions';
	import IconCloudUpload from '@hyvor/icons/IconCloudUpload';
	import type { Media } from '../../../../lib/types';
	import MediaFile from './MediaFile.svelte';
	import { uploadToMediaLibrary } from '../../../../lib/fileUploader';
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

	let extensions: string[] = [];
	let search: string | null = null;

	async function handleClickUpload() {
		const file = await uploadToMediaLibrary();
		if (file) {
			load();
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
