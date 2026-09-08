<script lang="ts">
	import { IconButton, Modal, SplitControl, TableRow } from '@hyvor/design/components';
	import type { Import } from '../../../../../lib/types';
	import JobStatusTag from '../../../../../lib/components/Tags/JobStatusTag.svelte';
	import dayjs from 'dayjs';
	import IconThreeDots from '@hyvor/icons/IconThreeDots';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();
	let showMore = $state(false);

	interface Props {
		data: Import;
	}

	let { data }: Props = $props();
</script>

<TableRow>
	<div>{data.name}</div>
	<div>{data.type}</div>
	<div>{dayjs.unix(data.created_at).fromNow()}</div>
	<div>
		<JobStatusTag status={data.status} />
	</div>
	<div>
		{data.status === 'completed'
			? i18n.t('console.tools.import.postsCount', { count: data.imported_counts.posts })
			: '-'}
	</div>
	<div>
		<IconButton size={20} variant="invisible" on:click={() => (showMore = !showMore)}>
			<IconThreeDots size={16} />
		</IconButton>
	</div>
</TableRow>

<Modal
	bind:show={showMore}
	title={i18n.t('console.tools.import.details')}
	footer={{
		cancel: {
			text: i18n.t('console.common.close')
		},
		confirm: false
	}}
	on:close={() => (showMore = false)}
>
	<SplitControl label={i18n.t('console.common.name')}>
		<span style="word-break:break-all">{data.name}</span>
	</SplitControl>

	<SplitControl label={i18n.t('console.tools.import.type')}>
		<span style="text-transform:capitalize">{data.type}</span>
	</SplitControl>

	<SplitControl label={i18n.t('console.common.status')}>
		<JobStatusTag status={data.status} />
	</SplitControl>

	<SplitControl label={i18n.t('console.tools.import.startedAt')}>
		{dayjs.unix(data.created_at).format('YYYY-MM-DD HH:mm')}
	</SplitControl>

	<SplitControl label={i18n.t('console.tools.import.options')}>
		<pre>{JSON.stringify(data.options, null, 2)}</pre>
	</SplitControl>

	<SplitControl label={i18n.t('console.tools.import.importedCounts')}>
		<div class="imported-counts">
			<div>
				<span>{i18n.t('console.nav.posts')}</span><span>{data.imported_counts.posts}</span>
			</div>
			<div>
				<span>{i18n.t('console.nav.pages')}</span><span>{data.imported_counts.pages}</span>
			</div>
			<div>
				<span>{i18n.t('console.settings.nav.users')}</span><span>{data.imported_counts.users}</span>
			</div>
			<div>
				<span>{i18n.t('console.settings.nav.tags')}</span><span>{data.imported_counts.tags}</span>
			</div>
		</div>
	</SplitControl>
</Modal>

<style>
	.imported-counts {
		div {
			margin-bottom: 10px;
		}
		span:first-child {
			display: inline-block;
			width: 100px;
			margin-right: 5px;
			font-weight: 600;
		}
	}
	pre {
		background-color: #fafafa;
		padding: 15px;
		border-radius: 20px;
		overflow: auto;
	}
</style>
