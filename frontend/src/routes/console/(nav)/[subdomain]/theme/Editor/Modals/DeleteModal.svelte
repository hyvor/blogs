<script lang="ts">
	import { Button, ButtonGroup, Modal, toast } from '@hyvor/design/components';
	import type { ThemeFile } from '../../../../../lib/types';
	import { deleteFile } from '../../themeActions';
	import { removeThemeFileStore, selectedThemeFileIdStore } from '../../themeStore';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		open?: boolean;
		file: ThemeFile;
	}

	let { open = $bindable(false), file }: Props = $props();

	function handleDelete() {
		open = false;
		const toastId = toast.loading(i18n.t('console.theme.deletingFile'));

		deleteFile(file.id).then(() => {
			toast.success(i18n.t('console.theme.fileDeleted'), { id: toastId });
			removeThemeFileStore(file.id);
			selectedThemeFileIdStore.set(null);
		});
	}
</script>

<Modal size="small" bind:show={open} title={i18n.t('console.theme.deleteFile')}>
	{i18n.t('console.theme.deleteFileConfirm')}

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (open = false)}>
				{i18n.t('console.common.cancel')}
			</Button>

			<Button color="red" on:click={handleDelete}>{i18n.t('console.common.delete')}</Button>
		</ButtonGroup>
	{/snippet}
</Modal>
