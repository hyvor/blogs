<script lang="ts">
	import {
		Button,
		IconMessage,
		Loader,
		Table,
		TableRow,
		toast,
		TabNav,
		TabNavItem,
		Dropdown,
		ActionList,
		ActionListItem,
		LoadButton
	} from '@hyvor/design/components';
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
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	let webhooks: Webhook[] = $state([]);
	let deliveries: WebhookDelivery[] = $state([]);

	let isWebhooksLoading = $state(true);
	let isDeliveriesLoading = $state(true);
	let isCreating = $state(false);
	let activeTab = $state<'configure' | 'deliveries'>('configure');
	let webhooksLoaded = $state(false);

	let selectedWebhookId = $state<number | null>(null);
	let showWebhookFilter = $state(false);

	let isLoadingMoreDeliveries = $state(false);
	let hasMoreDeliveries = $state(false);
	const deliveriesLimit = 50;

	let previousSelectedWebhookId = $state<number | null | undefined>(undefined);

	$effect(() => {
		if (activeTab === 'deliveries') {
			// Load webhooks first if not already loaded, then load deliveries
			if (!webhooksLoaded && !isWebhooksLoading) {
				loadWebhooks().then(() => {
					loadDeliveries();
					previousSelectedWebhookId = selectedWebhookId;
				});
			} else if (webhooksLoaded) {
				// Only load deliveries if this is the first time switching to deliveries tab
				// or if the webhook filter has actually changed
				if (
					previousSelectedWebhookId === undefined ||
					previousSelectedWebhookId !== selectedWebhookId
				) {
					loadDeliveries();
					previousSelectedWebhookId = selectedWebhookId;
				}
			}
		}
	});

	onMount(() => {
		loadWebhooks();
	});

	function loadWebhooks() {
		isWebhooksLoading = true;
		return getWebhooks()
			.then((webhookList) => {
				webhooks = webhookList;
				webhooksLoaded = true;
			})
			.catch((error) => {
				toast.error(i18n.t('console.settings.webhooks.failedToLoad', { message: error.message }));
			})
			.finally(() => {
				isWebhooksLoading = false;
			});
	}

	function loadDeliveries(more = false) {
		if (webhooks.length === 0) {
			isDeliveriesLoading = false;
			isLoadingMoreDeliveries = false;
			return;
		}
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
					toast.error(i18n.t('console.settings.webhooks.failedToLoadMoreDeliveries'));
				} else {
					toast.error(i18n.t('console.settings.webhooks.failedToLoadDeliveries'));
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

	function handleCreate(e: Webhook) {
		webhooks = [e, ...webhooks];
		isCreating = false;
	}

	function handleUpdate(e: Webhook) {
		webhooks = webhooks.map((webhook) => {
			if (webhook.id === e.id) {
				return e;
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
		if (!selectedWebhookId) return i18n.t('console.settings.webhooks.allWebhooks');
		const webhook = webhooks.find((w) => w.id === selectedWebhookId);
		return webhook ? webhook.url : i18n.t('console.settings.webhooks.unknownWebhook');
	}

	function truncateUrl(url: string, maxLength: number = 30): string {
		if (url.length <= maxLength) return url;
		return url.substring(0, maxLength) + '...';
	}
</script>

<SettingsTop>
	<div class="tabs">
		<TabNav>
			<TabNavItem
				name="configure"
				active={activeTab === 'configure'}
				onclick={() => (activeTab = 'configure')}
			>
				{i18n.t('console.settings.webhooks.configure')}
			</TabNavItem>
			<TabNavItem
				name="deliveries"
				active={activeTab === 'deliveries'}
				onclick={() => (activeTab = 'deliveries')}
			>
				{i18n.t('console.settings.webhooks.deliveries')}
			</TabNavItem>
		</TabNav>
	</div>
	{#if activeTab === 'configure'}
		<Button on:click={() => (isCreating = true)}>
			<IconPlus />
			{i18n.t('console.settings.webhooks.create')}
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
							{i18n.t('console.settings.webhooks.allWebhooks')}
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
		{#if isWebhooksLoading}
			<Loader full />
		{:else}
			<WebhookList
				{webhooks}
				{isWebhooksLoading}
				onDelete={(e) => handleDelete(e)}
				onUpdate={(e) => handleUpdate(e)}
			/>
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
	<CreateWebhookModal bind:show={isCreating} onCreate={handleCreate} />
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
