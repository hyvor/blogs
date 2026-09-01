<script lang="ts">
	import { IconMessage, TableRow } from '@hyvor/design/components';
	import type { Webhook } from '../../../../lib/types';
	import WebhookRow from './WebhookRow.svelte';
	import SettingsTable from '../@components/SettingsTable.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		webhooks: Webhook[];
		isWebhooksLoading: boolean;
		onDelete: (id: number) => void;
		onUpdate: (webhook: Webhook) => void;
	}

	let { webhooks, isWebhooksLoading, onDelete, onUpdate }: Props = $props();
</script>

{#if webhooks.length === 0}
	<IconMessage empty message={i18n.t('console.settings.webhooks.noWebhooks')} />
{:else}
	<SettingsTable columns="1fr 1fr 80px 80px">
		<TableRow head>
			<div>{i18n.t('console.tools.import.url')}</div>
			<div>{i18n.t('console.settings.webhooks.events')}</div>
			<div>{i18n.t('console.settings.webhooks.secret')}</div>
			<div></div>
		</TableRow>

		{#each webhooks as webhook (webhook.id)}
			<WebhookRow {webhook} {onDelete} {onUpdate} />
		{/each}
	</SettingsTable>
{/if}

<style>
</style>
