<script lang="ts">
	import { onMount } from 'svelte';
	import {
		ActionList,
		ActionListGroup,
		ActionListItem,
		Button,
		Dropdown,
		LoadButton,
		Loader,
		Switch,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import type { Blog } from '../types';
	import sudoApi from '../lib/sudoApi';
	import BlogRow from './BlogRow.svelte';
	import { goto } from '$app/navigation';

	let data: Blog[] = $state([]);

	const SORTS = {
		id: 'ID'
	};

	const LIMIT = 30;

	let sort: keyof typeof SORTS = $state('id');
	let sortAsc = $state(false);
	let sortsDropdownShow = $state(false);

	let loading = $state(false);
	let loadingMore = $state(false);
	let hasMore = $state(false);

	// Search
	type SearchByType = Record<string, string>;
	const SEARCH_BY: SearchByType = {
		blog_id: 'Blog ID',
		subdomain: 'Subdomain',
		user_id: 'User ID'
	};
	let searchBy: keyof SearchByType = $state('blog_id');
	let search = $state('');
	let byShow = $state(false);

	function load(more = false) {
		if (more) {
			loadingMore = true;
		} else {
			loading = true;
		}

		sudoApi
			.get<Blog[]>({
				endpoint: '/blogs',
				data: {
					limit: LIMIT,
					sort: sortAsc ? 'asc' : 'desc',
					offset: more ? data.length : 0
				}
			})
			.then((res) => {
				data = more ? [...data, ...res] : res;
				hasMore = res.length === LIMIT;
				loading = false;
				loadingMore = false;
			});
	}

	function onSortClick(val: any) {
		sort = val;
		load();
		sortsDropdownShow = false;
	}

	function onByChange(value: any) {
		searchBy = value;
		byShow = false;
	}

	function searchAction() {
		search = search.trim();

		if (search === '') {
			return;
		}

		sudoApi
			.get<Blog[]>({
				endpoint: '/blogs',
				data: {
					[searchBy]: search
				}
			})
			.then((res) => {
				if (res.length === 0) {
					toast.error('Blog not found for ' + SEARCH_BY[searchBy] + ': ' + search);
					return;
				}
				goto(`/sudo/blogs/${res[0].id}`);
			})
			.catch((err) => {
				toast.error(err.message);
			});
	}

	onMount(load);
</script>

<div class="header">
	<div class="search-section">
		<TextInput
			size="small"
			bind:value={search}
			placeholder="Search by {SEARCH_BY[searchBy]}..."
			onkeyup={(e) => e.key === 'Enter' && searchAction()}
		/>
		<Dropdown bind:show={byShow}>
			{#snippet trigger()}
				<Button size="small" color="input">
					{SEARCH_BY[searchBy]}
					{#snippet end()}
						<IconCaretDown size={12} />
					{/snippet}
				</Button>
			{/snippet}
			{#snippet content()}
				<ActionList>
					<ActionListGroup>
						{#each Object.entries(SEARCH_BY) as [key, value]}
							<ActionListItem on:select={() => onByChange(key)}>{value}</ActionListItem>
						{/each}
					</ActionListGroup>
				</ActionList>
			{/snippet}
		</Dropdown>
	</div>
	<div class="sort-section">
		<Dropdown bind:show={sortsDropdownShow}>
			{#snippet trigger()}
				<Button color="input" size="small">
					<b>Sort by</b>: {SORTS[sort]}
					{#snippet end()}
						<IconCaretDown size={14} />
					{/snippet}
				</Button>
			{/snippet}
			{#snippet content()}
				<ActionList>
					{#each Object.entries(SORTS) as [key, value]}
						<ActionListItem on:select={() => onSortClick(key)}>{value}</ActionListItem>
					{/each}
				</ActionList>
			{/snippet}
		</Dropdown>
		<Switch bind:checked={sortAsc} on:change={() => load()}>Ascending</Switch>
	</div>
</div>

<div class="wrap">
	{#if loading}
		<Loader padding={100} block />
	{:else}
		<div class="column-headers">
			<span>ID</span>
			<span>Blog</span>
			<span>Type</span>
			<span>Hosting URL</span>
		</div>
		{#each data as blog}
			<BlogRow {blog} />
		{/each}
		<LoadButton text="Load more" loading={loadingMore} show={hasMore} on:click={() => load(true)} />
	{/if}
</div>

<style>
	.header {
		padding: 20px;
		border-bottom: 1px solid var(--border);
		display: grid;
		grid-template-columns: minmax(100px, 400px) 1fr;
		gap: 20px;
		align-items: center;
	}
	.search-section {
		display: flex;
		gap: 8px;
		align-items: center;
	}
	.sort-section {
		display: flex;
		gap: 10px;
		align-items: center;
		justify-content: flex-end;
	}
	.wrap {
		padding: 20px;
		flex: 1;
		overflow: auto;
	}
	.column-headers {
		display: grid;
		padding: 10px 25px;
		grid-template-columns: 60px 1fr 100px 1fr;
		font-size: 10px;
		color: var(--text-light);
		text-transform: uppercase;
		position: sticky;
		top: 0;
	}
</style>
