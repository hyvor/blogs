<script lang="ts">
	import dayjs from 'dayjs';
	import type { Media } from '../../../../lib/types';
	import { IMAGE_EXTENSIONS, deleteMedia, updateMedia } from './mediaActions';
	import { IconButton, Tooltip, confirm, toast } from '@hyvor/design/components';
	import { IconTrash } from '@hyvor/icons';
	import { createEventDispatcher } from 'svelte';
	import MediaUpdateFileName from './MediaUpdateFileName.svelte';


	// If the user is selecting media files
	// delete button will not be shown
	
	interface Props {
		media: Media;
		// an event will be fired when the user selects a media file
		selecting?: boolean;
	}

	let { media, selecting = false }: Props = $props();

	let isEditingFileName = $state(false);

	const dispatch = createEventDispatcher<{
		select: Media;
		delete: Media;
		update: Media;
	}>();

	function handleFileNameClick() {
		isEditingFileName = true;
	}

	function handleClick(e: any) {
		if (selecting) {
			e.preventDefault();
			dispatch('select', media);
		}
	}

	async function handleDelete() {
		if (
			await confirm({
				title: 'Delete Media',
				content: 'Are you sure you want to delete this media file? This action cannot be undone!',
				confirmText: 'Yes, Delete',
				danger: true
			})
		) {
			const toastId = toast.loading('Deleting...');

			deleteMedia(media.id)
				.then(() => {
					toast.success('Deleted', { id: toastId });
					dispatch('delete', media);
				})
				.catch((err) => toast.error(err.message, { id: toastId }));
		}
	}

	const isImage = IMAGE_EXTENSIONS.indexOf(media.extension) !== -1;
	const uploadedAt = dayjs.unix(media.uploaded_at);
</script>

<div class="media-file">
	{#if isEditingFileName}
		<MediaUpdateFileName bind:show={isEditingFileName} {media} on:update />
	{/if}

	<a class="body" href={media.url} target="_blank" onclick={handleClick}>
		{#if isImage}
			<img src={media.url} alt={media.name} />
		{:else}
			<div class="extension">
				{media.extension}
			</div>
		{/if}
	</a>

	<div class="footer">
		<Tooltip text="Click to edit">
			<button class="media-name" title={media.name} onclick={handleFileNameClick}
				>{media.name}</button
			>
		</Tooltip>
		<time
			class="media-at"
			datetime={uploadedAt.format()}
			title={uploadedAt.format('YYYY-MM-DD HH:mm:ss')}>{uploadedAt.format('YYYY-MM-DD')}</time
		>
	</div>

	{#if !selecting}
		<span class="media-delete">
			<IconButton on:click={handleDelete} size="small" color="red" variant="invisible">
				<IconTrash size={10} />
			</IconButton>
		</span>
	{/if}
</div>

<style>
	.media-file {
		width: calc(25% - 10px);
		height: 240px;
		display: flex;
		flex-direction: column;
		position: relative;
	}

	.body {
		display: flex;
		align-items: center;
		justify-content: center;
		flex: 1;
		background-color: var(--input);
		border-radius: var(--box-radius);
		min-height: 0;
	}

	.footer {
		font-size: 12px;
		color: var(--text-light);
		text-align: center;
		margin-top: 5px;
		padding: 0 5px;
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	.media-name {
		overflow: hidden;
		text-overflow: ellipsis;
		font-weight: 600;
		word-break: break-all;
	}
	.media-name:hover {
		text-decoration: underline;
	}

	.media-delete {
		position: absolute;
		top: 10px;
		right: 10px;
	}

	.extension {
		font-size: 24px;
		font-weight: 600;
		color: var(--text-light);
	}

	img {
		max-width: 100%;
		max-height: 100%;
	}
</style>
