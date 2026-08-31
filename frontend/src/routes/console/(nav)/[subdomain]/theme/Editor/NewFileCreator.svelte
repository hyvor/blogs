<script lang="ts">
	import { Button, toast } from '@hyvor/design/components';
	import type { ThemeFolder } from '../../../../lib/types';
	import IconCloudUpload from '@hyvor/icons/IconCloudUpload';
	import IconPlus from '@hyvor/icons/IconPlus';

	import CreateEditModal from './Modals/CreateEditModal.svelte';
	import { getConfig } from '../../../../lib/config';
	import byteFormatter from '../../../../lib/helper/byte-formatter';
	import { createFile } from '../themeActions';
	import { addThemeFileToStore, selectedThemeFileIdStore } from '../themeStore';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		folder: ThemeFolder;
	}

	let { folder }: Props = $props();

	let uploadInput: HTMLInputElement | undefined = $state();

	let isCreating = $state(false);

	function handleUpload() {
		const files = uploadInput?.files;
		const file = files?.[0] || null;
		if (!file) {
			return toast.error(i18n.t('console.theme.selectFileToUpload'));
		}

		const maxSize = getConfig().limits.max_asset_file_size;
		if (file.size > maxSize) {
			return toast.error(i18n.t('console.theme.fileTooLarge', { size: byteFormatter(maxSize) }));
		}

		const toastId = toast.loading(i18n.t('console.theme.uploadingFile'));

		createFile(folder, file.name, file)
			.then((res) => {
				toast.success(i18n.t('console.theme.fileUploaded'), { id: toastId });
				addThemeFileToStore(res);
				selectedThemeFileIdStore.set(res.id);
			})
			.catch((err) => {
				toast.error(err.message || 'Unable to upload file', { id: toastId });
			});
	}

	function handleUploadClick() {
		uploadInput?.click();
	}
</script>

<div class="file-creator">
	<input type="file" bind:this={uploadInput} style="display: none;" onchange={handleUpload} />

	<Button size="x-small" variant="invisible" on:click={() => (isCreating = true)}>
		{#snippet start()}
			<IconPlus size={11} />
		{/snippet}
		{i18n.t('console.posts.new')}
	</Button>

	{#if folder === 'assets'}
		<Button size="x-small" variant="invisible" on:click={handleUploadClick}>
			{#snippet start()}
				<IconCloudUpload size={11} />
			{/snippet}
			{i18n.t('console.theme.upload')}
		</Button>
	{/if}
</div>

{#if isCreating}
	<CreateEditModal file={{ id: null, name: '', folder: folder }} bind:open={isCreating} />
{/if}

<style>
	.file-creator {
		margin-top: 3px;
		margin-bottom: 4px;
	}
</style>
