<script lang="ts">
	import {
		ActionListItem,
		Avatar,
		IconMessage,
		Loader,
		Text,
		TextInput
	} from '@hyvor/design/components';
	import type { User } from '../../../../../lib/types';
	import { createEventDispatcher, onMount } from 'svelte';
	import { getUsers } from '../../../settings/users/userActions';
	import { postListFiltersStore } from '../../postListStore';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	let isLoading = $state(true);
	let users: User[] = $state([]);
	let search = $state('');
	let input: HTMLInputElement;

	let err = $state(false);

	function loadUsers() {
		isLoading = true;
		users = [];

		getUsers({ search: search.trim() ? search.trim() : undefined })
			.then((res) => {
				users = res;
				isLoading = false;
			})
			.catch((_) => (err = true))
			.finally(() => (isLoading = false));
	}

	const dispatch = createEventDispatcher<{
		select: User;
	}>();

	function handleSelect(user: User) {
		dispatch('select', user);
	}

	let timeout: null | ReturnType<typeof setTimeout> = null;

	function handleSearchInput() {
		if (timeout) clearTimeout(timeout);

		timeout = setTimeout(() => {
			loadUsers();
		}, 500);
	}

	onMount(() => {
		loadUsers();
		input?.focus();

		return () => {
			if (timeout) clearTimeout(timeout);
		};
	});
</script>

<TextInput
	block
	placeholder={i18n.t('console.posts.filters.searchAuthorPlaceholder')}
	autofocus
	bind:value={search}
	bind:input
	on:input={handleSearchInput}
/>

<div class="results">
	{#if isLoading}
		<Loader block padding={35} size="small" />
	{:else if err}
		<IconMessage error padding={35} />
	{:else if users.length === 0}
		<IconMessage
			empty
			padding={35}
			message={i18n.t('console.posts.noAuthorsFound')}
			iconSize={30}
		/>
	{:else}
		{#each users as user (user.id)}
			{@const username = user.variants[0]?.name || ''}
			<ActionListItem
				on:click={() => handleSelect(user)}
				selected={$postListFiltersStore.author?.id === user.id}
			>
				{#snippet start()}
					<Avatar src={user.picture_url} alt={username} {username} size={20} />
				{/snippet}
				<span class="text">
					{user.variants[0]?.name || 'Unnamed'}
				</span>
				{#snippet end()}
					<Text light small>
						{i18n.t('console.posts.filters.postsCount', { count: user.posts_count })}
					</Text>
				{/snippet}
			</ActionListItem>
		{/each}
	{/if}
</div>

<style>
	.results {
		margin-top: 10px;
		max-height: 350px;
		overflow: auto;
	}
</style>
