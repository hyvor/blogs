<script lang="ts">
	import { onMount } from 'svelte';
	import {
		ActionList,
		ActionListGroup,
		ActionListItem,
		Button,
		Dropdown,
		LoadButton,
		Switch,
		TextInput,
		toast,
		Validation
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
	let searchError = $state('');
	let byShow = $state(false);

	function load(more = false) {
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
		searchError = '';
		search = search.trim();

		if (search === '') {
			searchError = 'Search field is required';
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

<div class="wrap">
	<div class="top">
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

	<div class="list">
		{#each data as blog}
			<BlogRow {blog} />
		{/each}

		<LoadButton text="Load more" loading={false} show={hasMore} on:click={() => load(true)} />
	</div>
</div>

<div class="search-bar">
	<TextInput
		size="small"
		bind:value={search}
		placeholder="Search"
		state={searchError ? 'error' : undefined}
		on:keyup={(e) => e.key === 'Enter' && searchAction()}
	/>

	<Dropdown position="top" align="center" bind:show={byShow}>
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

	<Button size="small" on:click={searchAction}>Search</Button>

	{#if searchError}
		<div style="margin-top:10px;">
			<Validation state="error" style="display:inline-flex">{searchError}</Validation>
		</div>
	{/if}
</div>

<style>
	.wrap {
		padding: 30px;
		flex: 1;
		overflow: auto;
	}
	.top {
		display: flex;
		align-items: center;
		gap: 10px;
		margin-bottom: 20px;
	}
	.list {
		padding: 5px 0;
	}
	.search-bar {
		padding: 15px 25px;
		text-align: center;
		border-top: 1px solid var(--border);
	}
	.search-bar :global(input) {
		width: 250px;
	}
</style>
