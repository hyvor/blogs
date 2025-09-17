<script lang="ts">
	import { Button, IconMessage, Loader, Table, TableRow, toast, TabNav, TabNavItem, Dropdown, ActionList, ActionListItem, LoadButton} from '@hyvor/design/components';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import type { Webhook, WebhookDelivery } from '../../../../lib/types';
	import { onMount } from 'svelte';
	import { getWebhooks } from './webhookActions';
	import { getWebhookDeliveries } from './webhookDeliveryActions';
	import CreateWebhookModal from './CreateUpdateWebhookModal.svelte';
	import WebhookList from './WebhookList.svelte';
	import WebhookDeliveryList from './WebhookDeliveryList.svelte';

	let webhooks: Webhook[] = $state([]);
	let deliveries: WebhookDelivery[] = $state([]);

	let isLoading = $state(true);
	let isDeliveriesLoading = $state(true);
	let isCreating = $state(false);
	let activeTab = $state<'configure' | 'deliveries'>('configure');
	
	let selectedWebhookId = $state<number | null>(null);
	let showWebhookFilter = $state(false);
	
	let isLoadingMoreDeliveries = $state(false);
	let hasMoreDeliveries = $state(false);
	const deliveriesLimit = 50;

	$effect(() => {
		if (activeTab === 'deliveries') {
			// Load webhooks first if not already loaded, then load deliveries
			if (webhooks.length === 0 && !isLoading) {
				loadWebhooks().then(() => {
					loadDeliveries();
				});
			} else {
				loadDeliveries();
			}
		}
	});

	// Reload deliveries when webhook filter changes
	$effect(() => {
		if (activeTab === 'deliveries' && selectedWebhookId !== undefined) {
			loadDeliveries();
		}
	});

	onMount(() => {
		loadWebhooks();
	});

	function loadWebhooks() {
		isLoading = true;
		return getWebhooks()
			.then((webhookList) => {
				webhooks = webhookList;
			})
			.catch((error) => {
				toast.error('Failed to load webhooks: ' + error.message);
			})
			.finally(() => {
				isLoading = false;
			});
	}

	function loadDeliveries(more = false) {
		more ? (isLoadingMoreDeliveries = true) : (isDeliveriesLoading = true);
		if (!more) deliveries = [];

		const offset = more ? deliveries.length : 0;

		getWebhookDeliveries(selectedWebhookId || undefined, deliveriesLimit, offset)
			.then((response) => {
				const newDeliveries = response;
				deliveries = more ? [...deliveries, ...newDeliveries] : newDeliveries;
				hasMoreDeliveries = newDeliveries.length === deliveriesLimit;
			})
			.catch((error) => {
				if (more) {
					toast.error('Failed to load more deliveries');
				} else {
					toast.error('Failed to load webhook deliveries');
				}
			})
			.finally(() => {
				isDeliveriesLoading = false;
				isLoadingMoreDeliveries = false;
			});
	}


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

	function handleWebhookFilterSelect(webhookId: number | null) {
		selectedWebhookId = webhookId;
		showWebhookFilter = false;
		// Reset pagination when filter changes
		hasMoreDeliveries = false;
	}

	function getSelectedWebhookUrl(): string {
		if (!selectedWebhookId) return 'All Webhooks';
		const webhook = webhooks.find(w => w.id === selectedWebhookId);
		return webhook ? webhook.url : 'Unknown Webhook';
	}

	function truncateUrl(url: string, maxLength: number = 30): string {
		if (url.length <= maxLength) return url;
		return url.substring(0, maxLength) + '...';
	}
</script>

<SettingsTop>
	<div class="tabs">
		<TabNav bind:active={activeTab}>
			<TabNavItem name="configure">Configure</TabNavItem>
			<TabNavItem name="deliveries">Deliveries</TabNavItem>
		</TabNav>
	</div>
	{#if activeTab === 'configure'}
		<Button on:click={() => (isCreating = true)}>
			<IconPlus />
			Create Webhook
		</Button>
	{:else if activeTab === 'deliveries'}
		<div class="filter-section">
			<Dropdown bind:show={showWebhookFilter} width={300}>
				{#snippet trigger()}
					<Button color="input">
						{truncateUrl(getSelectedWebhookUrl())}
						{#snippet end()}
							<IconCaretDown />
						{/snippet}
					</Button>
				{/snippet}
				{#snippet content()}
					<ActionList selection="single">
						<ActionListItem 
							selected={selectedWebhookId === null} 
							on:select={() => handleWebhookFilterSelect(null)}
						>
							All Webhooks
						</ActionListItem>
						{#each webhooks as webhook (webhook.id)}
							<ActionListItem 
								selected={selectedWebhookId === webhook.id} 
								on:select={() => handleWebhookFilterSelect(webhook.id)}
							>
								{webhook.url}
							</ActionListItem>
						{/each}
					</ActionList>
				{/snippet}
			</Dropdown>
		</div>
	{/if}
</SettingsTop>

<div class="content">
	{#if activeTab === 'configure'}
		{#if isLoading}
			<Loader full />
		{:else}
			<WebhookList {webhooks} {isLoading} on:delete={(e) => handleDelete(e.detail)} on:update={handleUpdate} />
		{/if}
	{:else if activeTab === 'deliveries'}
		{#if isDeliveriesLoading}
			<Loader full />
		{:else}
			<WebhookDeliveryList 
				{deliveries} 
				hasMore={hasMoreDeliveries}
				isLoadingMore={isLoadingMoreDeliveries}
				on:click={() => loadDeliveries(true)}
			/>
		{/if}
	{/if}
</div>

{#if isCreating}
	<CreateWebhookModal bind:show={isCreating} on:create={handleCreate} />
{/if}

<style>
	.content {
		padding: 15px 30px;
		flex: 1;
		overflow: auto;
	}

	.tabs {
		flex: 1;
	}

	.filter-section {
		display: flex;
		align-items: center;
	}
</style>
