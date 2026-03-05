<script lang="ts">
	import {
		Button,
		IconButton,
		TableRow,
		Tag,
		Tooltip,
		confirm,
		toast
	} from '@hyvor/design/components';
	import type { Webhook } from '../../../../lib/types';
	import IconPencilFill from '@hyvor/icons/IconPencilFill';
	import IconTrash from '@hyvor/icons/IconTrash';

	import { deleteWebhook } from './webhookActions';
	import { createEventDispatcher } from 'svelte';
	import CreateUpdateWebhookModal from './CreateUpdateWebhookModal.svelte';

	interface Props {
		webhook: Webhook;
		onDelete: (id: number) => void;
		onUpdate: (webhook: Webhook) => void;
	}

	let { webhook, onDelete, onUpdate }: Props = $props();

	function handleCopy() {
		navigator.clipboard.writeText(webhook.secret);
		toast.success('Copied');
	}

	let isUpdating = $state(false);

	async function handleDelete() {
		if (
			await confirm({
				title: 'Delete Webhook',
				content: 'Are you sure you want to delete this webhook?',
				confirmText: 'Yes, Delete',
				danger: true
			})
		) {
			const toastId = toast.loading('Deleting webhook');

			deleteWebhook(webhook.id)
				.then(() => {
					toast.success('Webhook deleted', { id: toastId });
				})
				.catch((err) => {
					toast.error(err.message, { id: toastId });
				});

			onDelete(webhook.id);
		}
	}
</script>

<TableRow>
	<div>{webhook.url}</div>
	<div class="events">
		{#each webhook.events as event (event)}
			<Tag size="small">{event}</Tag>
		{/each}
	</div>
	<div>
		<Button size="x-small" on:click={handleCopy}>COPY</Button>
	</div>
	<div>
		<Tooltip text="Edit Webhook">
			<IconButton
				size="small"
				variant="fill-light"
				color="gray"
				on:click={() => (isUpdating = true)}
			>
				<IconPencilFill size={10} />
			</IconButton>
		</Tooltip>

		<Tooltip text="Delete Webhook">
			<IconButton size="small" variant="fill-light" color="red" on:click={handleDelete}>
				<IconTrash size={10} />
			</IconButton>
		</Tooltip>
	</div>
</TableRow>

{#if isUpdating}
	<CreateUpdateWebhookModal {webhook} bind:show={isUpdating} {onUpdate} />
{/if}

<style>
	.events {
		display: flex;
		flex-wrap: wrap;
		gap: 4px;
	}
</style>
