<script lang="ts">
	import { IconMessage, Table, TableRow } from '@hyvor/design/components';
	import type { Webhook } from '../../../../lib/types';
	import WebhookRow from './WebhookRow.svelte';

	interface Props {
		webhooks: Webhook[];
		isWebhooksLoading: boolean;
		onDelete: (id: number) => void;
		onUpdate: (webhook: Webhook) => void;
	}

	let { webhooks, isWebhooksLoading, onDelete, onUpdate }: Props = $props();
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
			<WebhookRow {webhook} {onDelete} {onUpdate} />
		{/each}
	</Table>
{/if}

<style>
</style>
