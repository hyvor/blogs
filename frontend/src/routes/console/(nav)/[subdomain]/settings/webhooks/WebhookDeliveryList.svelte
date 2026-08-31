<script lang="ts">
	import { IconMessage, TableRow, TableCell, Tag, LoadButton } from '@hyvor/design/components';
	import type { WebhookDelivery } from '../../../../lib/types';
	import dayjs from 'dayjs';
	import SettingsTable from '../@components/SettingsTable.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		deliveries: WebhookDelivery[];
		hasMore?: boolean;
		isLoadingMore?: boolean;
	}

	let { deliveries, hasMore = false, isLoadingMore = false }: Props = $props();

	function getStatusColor(status: string) {
		switch (status) {
			case 'success':
				return 'green';
			case 'failed':
				return 'red';
			case 'pending':
				return 'orange';
			default:
				return 'default';
		}
	}

	function truncateUrl(url: string, maxLength: number = 150) {
		if (url.length <= maxLength) return url;
		return url.substring(0, maxLength) + '...';
	}
</script>

{#if deliveries.length === 0}
	<IconMessage empty message={i18n.t('console.settings.webhooks.noDeliveries')} />
{:else}
	<SettingsTable columns="2fr 1fr 1fr 1fr">
		<TableRow head>
			<TableCell>{i18n.t('console.tools.import.url')}</TableCell>
			<TableCell>{i18n.t('console.settings.webhooks.event')}</TableCell>
			<TableCell>{i18n.t('console.common.status')}</TableCell>
			<TableCell>Created</TableCell>
		</TableRow>
		{#each deliveries as delivery (delivery.id)}
			<TableRow>
				<TableCell>
					{truncateUrl(delivery.url)}
				</TableCell>
				<TableCell>
					<Tag variant="gray" size="small">{delivery.event}</Tag>
				</TableCell>
				<TableCell>
					<Tag color={getStatusColor(delivery.status)} size="small">
						{delivery.status}
					</Tag>
				</TableCell>
				<TableCell>
					{dayjs.unix(delivery.created_at).format('MMM D, YYYY')}
				</TableCell>
			</TableRow>
		{/each}
	</SettingsTable>

	<LoadButton
		text={i18n.t('console.common.loadMore')}
		show={hasMore}
		loading={isLoadingMore}
		on:click
	/>
{/if}
