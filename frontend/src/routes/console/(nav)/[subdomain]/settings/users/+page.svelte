<script lang="ts">
	import type { User, UserVariant } from '../../../../lib/types';
	import IconPlus from '@hyvor/icons/IconPlus';
	import {
		Button,
		Loader,
		IconMessage,
		TableRow,
		LoadButton,
		toast,
		Modal
	} from '@hyvor/design/components';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import SettingsTable from '../@components/SettingsTable.svelte';
	import UserRow from './UserRow.svelte';
	import { getUsers } from './userActions';
	import { onMount } from 'svelte';
	import AddUser from './AddUser.svelte';
	import { getI18n } from '../../../../lib/i18n';
	import { cant, redirectIfCant } from '../../../../lib/scope.svelte';

	const i18n = getI18n();

	let isLoading = $state(true);
	let isCreating = $state(false);
	let hasMore = $state(false);
	let isLoadingMore = $state(false);

	let users: User[] = $state([]);

	const limit = 20;

	function loadUsers(more = false) {
		more ? (isLoadingMore = true) : (isLoading = true);

		getUsers({
			limit,
			offset: more ? users.length : 0
		})
			.then((res) => {
				users = more ? [...users, ...res] : res;
				hasMore = res.length === limit;
			})
			.catch((e) => {
				if (!more) users = [];
				toast.error(e.message || 'Failed to load users.');
			})
			.finally(() => {
				isLoading = false;
				isLoadingMore = false;
			});
	}

	function handleAdd(e: CustomEvent<User>) {
		users = [e.detail, ...users];
	}

	function handleDelete(e: CustomEvent<number>) {
		users = users.filter((t) => t.id !== e.detail);
	}

	function handleCreateVariant(e: CustomEvent<{ id: number; variant: UserVariant }>) {
		users = users.map((t) => {
			const newTag =
				t.id === e.detail.id ? { ...t, variants: [...t.variants, e.detail.variant] } : t;
			return newTag;
		});
	}

	function handleUpdate(e: CustomEvent<User>) {
		users = users.map((t) => (t.id === e.detail.id ? e.detail : t));
	}

	onMount(() => {
		redirectIfCant('users.read');
		loadUsers();
	});
</script>

<SettingsTop>
	<Button disabled={cant('users.add')} on:click={() => (isCreating = true)}>
		Add User {#snippet end()}
			<IconPlus />
		{/snippet}
	</Button>
</SettingsTop>

<div class="table">
	{#if isLoading}
		<Loader full />
	{:else if users.length === 0}
		<IconMessage empty message={i18n.t('console.common.noUsersFound')} />
	{:else}
		<SettingsTable columns="2fr 1fr 1fr 1fr 70px">
			<TableRow head>
				<div>{i18n.t('console.common.slug')}</div>
				<div>{i18n.t('console.common.status')}</div>
				<div>{i18n.t('console.settings.users.role')}</div>
				<div>{i18n.t('console.nav.posts')}</div>
				<div></div>
			</TableRow>

			{#each users as user}
				<UserRow
					{user}
					on:delete={handleDelete}
					on:update={handleUpdate}
					on:variantCreate={handleCreateVariant}
				/>
			{/each}

			<LoadButton
				text={i18n.t('console.common.loadMore')}
				show={hasMore}
				on:click={() => loadUsers(true)}
				loading={isLoadingMore}
			/>
		</SettingsTable>
	{/if}
</div>

{#if isCreating}
	<AddUser bind:show={isCreating} on:add={handleAdd} />
{/if}

<style>
	.table {
		flex: 1;
		padding: 15px 30px;
	}
</style>
