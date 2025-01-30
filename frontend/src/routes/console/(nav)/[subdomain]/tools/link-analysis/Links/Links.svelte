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
	interface Props {
		stats: Stats;
	}

	let { stats }: Props = $props();

	let type: FilterType = $state(null);

	const types = [null, 'ok', 'broken', 'redirect', 'ignored'] as FilterType[];

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
				toast.error(e.message || 'Failed to load links.');
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
				return `OK (${stats.counts.ok})`;
			case 'broken':
				return `Broken (${stats.counts.broken})`;
			case 'redirect':
				return `Redirect (${stats.counts.redirect})`;
			case 'ignored':
				return `Ignored (${stats.counts.ignored})`;
			default:
				return `All (${all})`;
		}
	}

	function handelLinkUpdate(e: CustomEvent<Partial<LinkAnalysisLink>>) {
		const link = e.detail;

		links = links.map((l) => {
			if (l.id === link.id) {
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
				<div>Post</div>
				<div>Link</div>
				<div>Status</div>
				<div>Actions</div>
			</TableRow>

			{#each links as link (link.id)}
				<LinkRow {link} on:update={handelLinkUpdate} />
			{/each}
		</Table>

		<LoadButton
			text="Load More"
			on:click={() => loadLinks(true)}
			loading={isMoreLoading}
			show={hasMore}
		/>
	{:else}
		<IconMessage empty message="No links found." padding={100} />
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
