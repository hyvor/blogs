<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import FileUploader from '../../../../lib/components/FileUploader/FileUploader.svelte';
	import type { SelectedFile } from '../../../../lib/components/FileUploader/image-uploader';
	import { createEventDispatcher } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

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
		<img {src} alt={i18n.t('console.tools.media.uploaded')} />
	</div>

	<div class="buttons">
		<Button on:click={() => (isUploading = true)} size="x-small" variant="fill-light">
			{i18n.t('console.theme.change')}
		</Button>
		<Button on:click={handleRemove} size="x-small" color="red" variant="fill-light">
			{i18n.t('console.common.remove')}
		</Button>
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
		display: flex;
		gap: 2px;
	}
</style>
