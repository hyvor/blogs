<script lang="ts">
	import type { LinkAnalysisLink } from '../../../../../lib/types';
	import { getLinks, type FilterType, type Stats } from '../linkAnalysisActions';
	import {
		Button,
		ButtonGroup,
		Loader,
		Table,
		TableRow,
		toast,
		LoadButton,
		IconMessage
	} from '@hyvor/design/components';
	import LinkRow from './LinkRow.svelte';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		stats: Stats;
	}

	let { stats }: Props = $props();

	let type: FilterType = $state(null);

	const types = [null, 'ok', 'broken', 'risky', 'redirect', 'ignored'] as FilterType[];

	let isLoading = $state(true);
	let isMoreLoading = $state(false);
	let hasMore = $state(false);
	let links: LinkAnalysisLink[] = $state([]);

	const limit = 40;

	function loadLinks(more = false) {
		more ? (isMoreLoading = true) : (isLoading = true);

		getLinks({
			limit,
			offset: more ? links.length : 0,
			type
		})
			.then((res) => {
				links = more ? [...links, ...res] : res;
				hasMore = res.length === limit;
			})
			.catch((e) => {
				if (!more) links = [];
				toast.error(e.message || i18n.t('console.tools.linkAnalysis.failedToLoadLinks'));
			})
			.finally(() => {
				isMoreLoading = false;
				isLoading = false;
			});
	}

	$effect(() => {
		type;
		loadLinks();
	});

	function getButtonLabel(t: FilterType) {
		const all = Object.values(stats.counts).reduce((a, b) => a + b, 0);

		switch (t) {
			case 'ok':
				return `${i18n.t('console.tools.linkAnalysis.status.ok')} (${stats.counts.ok})`;
			case 'broken':
				return `${i18n.t('console.tools.linkAnalysis.status.broken')} (${stats.counts.broken})`;
			case 'risky':
				return `${i18n.t('console.tools.linkAnalysis.status.risky')} (${stats.counts.risky})`;
			case 'redirect':
				return `${i18n.t('console.tools.linkAnalysis.status.redirect')} (${stats.counts.redirect})`;
			case 'ignored':
				return `${i18n.t('console.tools.linkAnalysis.status.ignored')} (${stats.counts.ignored})`;
			default:
				return `${i18n.t('console.common.all')} (${all})`;
		}
	}

	function handelLinkUpdate(e: CustomEvent<Partial<LinkAnalysisLink>>) {
		const link = e.detail;

		links = links.map((l) => {
			if (l.post_variant_id === link.post_variant_id && l.url === link.url) {
				return {
					...l,
					...link
				};
			}

			return l;
		});
	}
</script>

<div class="nav">
	<ButtonGroup>
		{#each types as buttonType}
			<Button
				size="small"
				color={type === buttonType ? 'accent' : 'input'}
				on:click={() => (type = buttonType)}
			>
				{getButtonLabel(buttonType)}
			</Button>
		{/each}
	</ButtonGroup>
</div>

<div class="content">
	{#if isLoading}
		<Loader block padding={100} />
	{:else if links.length}
		<Table columns="1fr 2fr 100px 100px">
			<TableRow head>
				<div>{i18n.t('console.tools.linkAnalysis.post')}</div>
				<div>{i18n.t('console.tools.linkAnalysis.link')}</div>
				<div>{i18n.t('console.common.status')}</div>
				<div>{i18n.t('console.common.actions')}</div>
			</TableRow>

			{#each links as link (link.post_variant_id + link.url)}
				<LinkRow {link} on:update={handelLinkUpdate} />
			{/each}
		</Table>

		<LoadButton
			text={i18n.t('console.common.loadMore')}
			on:click={() => loadLinks(true)}
			loading={isMoreLoading}
			show={hasMore}
		/>
	{:else}
		<IconMessage empty message={i18n.t('console.tools.linkAnalysis.noLinks')} padding={100} />
	{/if}
</div>

<style>
	.nav {
		text-align: right;
	}
	.content {
		margin-top: 20px;
	}
</style>
