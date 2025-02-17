<script lang="ts">
	import { IconMessage, Loader, Table, TableRow, toast } from '@hyvor/design/components';
	import { getChecks } from '../linkAnalysisActions';
	import LinkStatusTag from '../Links/LinkStatusTag.svelte';
	import dayjs from 'dayjs';
	import JobStatusTag from '../../../../../lib/components/Tags/JobStatusTag.svelte';
	import { onMount } from 'svelte';
	import { linkAnalysisChecks } from '../linkAnalysisActions';

	let isLoading = $state(true);

	onMount(() => {
		getChecks()
			.then((res) => {
				linkAnalysisChecks.set(res);
			})
			.catch(() => {
				toast.error('Failed to load link analysis checks.');
			})
			.finally(() => {
				isLoading = false;
			});
	});
</script>

{#if isLoading}
	<Loader padding={60} block />
{:else if $linkAnalysisChecks.length}
	<Table columns="2fr 2fr 2fr 2fr 2fr 2fr 2fr 2fr">
		<TableRow head>
			<div>Date</div>
			<div>Status</div>
			<div>No. Posts</div>
			<div>
				<LinkStatusTag type="ok" showTooltip={false} />
			</div>
			<div>
				<LinkStatusTag type="broken" showTooltip={false} />
			</div>
			<div>
				<LinkStatusTag type="risky" showTooltip={false} />
			</div>
			<div>
				<LinkStatusTag type="redirect" showTooltip={false} />
			</div>
			<div>
				<LinkStatusTag type="ignored" showTooltip={false} />
			</div>
		</TableRow>

		{#each $linkAnalysisChecks as check (check.id)}
			<TableRow>
				<div>
					<time dateTime={dayjs.unix(check.created_at).toISOString()}>
						{dayjs.unix(check.created_at).format('YYYY-MM-DD')}
					</time>
				</div>

				<div>
					<JobStatusTag status={check.status} />
				</div>

				<div>
					<div>{check.posts_count}</div>
				</div>

				<div>
					{check.links_ok_count}
				</div>

				<div>
					{check.links_broken_count}
				</div>

				<div>
					{check.links_risky_count}
				</div>

				<div>
					{check.links_redirect_count}
				</div>

				<div>
					{check.links_ignored_count}
				</div>
			</TableRow>
		{/each}
	</Table>
{:else}
	<IconMessage empty padding={60} message="No Analyses Yet" />
{/if}
