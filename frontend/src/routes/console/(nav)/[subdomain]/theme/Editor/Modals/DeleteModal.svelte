<script lang="ts">
	import { Button, Modal, toast } from '@hyvor/design/components';
	import type { ThemeFile } from '../../../../../lib/types';
	import { deleteFile } from '../../themeActions';
	import { removeThemeFileStore, selectedThemeFileIdStore } from '../../themeStore';

	interface Props {
		open?: boolean;
		file: ThemeFile;
	}

	let { open = $bindable(false), file }: Props = $props();

	function handleDelete() {
		open = false;
		const toastId = toast.loading('Deleting file...');

		deleteFile(file.id).then(() => {
			toast.success('File deleted successfully', { id: toastId });
			removeThemeFileStore(file.id);
			selectedThemeFileIdStore.set(null);
		});
	}
</script>

<Modal size="small" bind:show={open} title="Delete file">
	Are you sure to delete this file?

	{#snippet footer()}
		<Button variant="invisible" on:click={() => (open = false)}>Cancel</Button>

		<Button color="red" on:click={handleDelete}>Delete</Button>
	{/snippet}
</Modal>
