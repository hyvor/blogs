<script lang="ts">
	import { Button, IconMessage, Loader, Table, TableRow, toast } from '@hyvor/design/components';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import type { Webhook } from '../../../../lib/types';
	import { onMount } from 'svelte';
	import { getWebhooks } from './webhookActions';
	import CreateWebhookModal from './CreateUpdateWebhookModal.svelte';
	import WebhookRow from './WebhookRow.svelte';

	let isLoading = $state(true);
	let isCreating = $state(false);

	let webhooks: Webhook[] = $state([]);

	onMount(() => {
		getWebhooks()
			.then((res) => {
				webhooks = res;
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				isLoading = false;
			});
	});

	function handleDelete(id: number) {
		webhooks = webhooks.filter((webhook) => webhook.id !== id);
	}

	function handleCreate(e: CustomEvent<Webhook>) {
		webhooks = [e.detail, ...webhooks];
		isCreating = false;
	}

	function handleUpdate(e: CustomEvent<Webhook>) {
		webhooks = webhooks.map((webhook) => {
			if (webhook.id === e.detail.id) {
				return e.detail;
			}
			return webhook;
		});
	}
</script>

<SettingsTop>
	<Button on:click={() => (isCreating = true)}>
		Create Webhook {#snippet end()}
			<IconPlus />
		{/snippet}
	</Button>
</SettingsTop>

<div class="webhooks">
	{#if isLoading}
		<Loader full />
	{:else if webhooks.length === 0}
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
</div>

{#if isCreating}
	<CreateWebhookModal bind:show={isCreating} on:create={handleCreate} />
{/if}

<style>
	.webhooks {
		padding: 15px 30px;
		flex: 1;
		overflow: auto;
	}
</style>
