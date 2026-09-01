<script lang="ts">
	import { IconButton, TableRow, Tooltip, confirm, toast } from '@hyvor/design/components';
	import type { ApiKey } from '../../../../lib/types';
	import IconArrowCounterclockwise from '@hyvor/icons/IconArrowCounterclockwise';
	import IconCopy from '@hyvor/icons/IconCopy';
	import IconTrash from '@hyvor/icons/IconTrash';

	import { deleteApiKey, regenerateApiKey } from './apiKeysActions';
	import { createEventDispatcher } from 'svelte';

	interface Props {
		apiKey: ApiKey;
	}

	let { apiKey }: Props = $props();

	const dispatch = createEventDispatcher();

	function handleCopy() {
		navigator.clipboard.writeText(apiKey.api_key);
		toast.success('Copied');
	}

	async function handleRegenerate() {
		if (
			await confirm({
				title: 'Regenerate API Key',
				content: 'Are you sure to regenerate this API Key? This will invalidate the old API Key.',
				confirmText: 'Yes, regenerate',
				danger: true
			})
		) {
			const toastId = toast.loading('Regenerating...');

			regenerateApiKey(apiKey.id)
				.then((newApiKey) => {
					dispatch('update', newApiKey);
					toast.success('Regenerated', { id: toastId });
				})
				.catch((err) => {
					toast.error(err.message, { id: toastId });
				});
		}
	}

	async function handleDelete() {
		if (
			await confirm({
				title: 'Delete API Key',
				content: 'Are you sure to delete this API Key? This cannot be undone.',
				confirmText: 'Yes, delete',
				danger: true
			})
		) {
			const toastId = toast.loading('Deleting...');

			deleteApiKey(apiKey.id)
				.then(() => {
					toast.success('Deleted', { id: toastId });
					dispatch('delete', apiKey.id);
				})
				.catch((err) => {
					toast.error(err.message, { id: toastId });
				});
		}
	}
</script>

<TableRow>
	<div>{apiKey.name}</div>
	<div class="api-type">{apiKey.type} API</div>
	<div>
		<Tooltip text="Copy API Key">
			<IconButton color="input" variant="fill" size="small" on:click={handleCopy}>
				<IconCopy size={12} />
			</IconButton>
		</Tooltip>

		<Tooltip text="Regenerate API Key">
			<IconButton color="input" variant="fill" size="small" on:click={handleRegenerate}>
				<IconArrowCounterclockwise size={12} />
			</IconButton>
		</Tooltip>

		<Tooltip text="Delete API Key">
			<IconButton variant="fill-light" color="red" size="small" on:click={handleDelete}>
				<IconTrash size={12} />
			</IconButton>
		</Tooltip>
	</div>
</TableRow>

<style>
	.api-type {
		text-transform: capitalize;
	}
</style>
