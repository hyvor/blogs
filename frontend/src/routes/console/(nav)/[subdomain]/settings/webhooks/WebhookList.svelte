<script lang="ts">
	import { IconMessage, Table, TableRow } from '@hyvor/design/components';
	import type { Webhook } from '../../../../lib/types';
	import WebhookRow from './WebhookRow.svelte';
	import { createEventDispatcher } from 'svelte';

	interface Props {
		webhooks: Webhook[];
		isLoading: boolean;
	}

	let { webhooks, isLoading }: Props = $props();
	const dispatch = createEventDispatcher();

	function handleDelete(id: number) {
		dispatch('delete', id);
	}

	function handleUpdate(e: CustomEvent<Webhook>) {
		dispatch('update', e.detail);
	}
</script>

{#if webhooks.length === 0}
	<IconMessage empty message="No Webhooks configured" />
{:else}
	<Table columns="1fr 1fr 80px 80px">
		<TableRow head>
			<div>URL</div>
			<div>Events</div>
			<div>Secret</div>
			<div></div>
		</TableRow>

		{#each webhooks as webhook (webhook.id)}
			<WebhookRow {webhook} on:delete={() => handleDelete(webhook.id)} on:update={handleUpdate} />
		{/each}
	</Table>
{/if}

<style>
</style>
