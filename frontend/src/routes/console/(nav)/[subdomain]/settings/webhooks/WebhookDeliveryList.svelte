<script lang="ts">
	import { IconMessage, Table, TableRow, TableCell, Tag, LoadButton } from '@hyvor/design/components';
	import type { WebhookDelivery } from '../../../../lib/types';
	import dayjs from 'dayjs';

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
	<IconMessage empty size="large" message="No webhook deliveries found" />
{:else}
	<Table columns="2fr 1fr 1fr 1fr" hover>
		<TableRow head>
			<TableCell>URL</TableCell>
			<TableCell>Event</TableCell>
			<TableCell>Status</TableCell>
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
	</Table>
	
	<LoadButton
		text="Load more"
		show={hasMore}
		loading={isLoadingMore}
		on:click
	/>
{/if}

<style>
</style>
